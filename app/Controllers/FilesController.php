<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\{Auth, Db, Config};

class FilesController
{
    public function download(string $token): void
    {
        $db = Db::instance();
        $file = $db->fetchOne("SELECT * FROM attachments WHERE token = ?", [$token]);
        if (!$file) { http_response_code(404); echo 'Файл не знайдено'; return; }

        $storagePath = Config::get('storage_path', dirname(__DIR__, 2) . '/storage');
        $path = $storagePath . '/uploads/' . $file['stored_name'];
        if (!is_file($path)) { http_response_code(404); echo 'Файл відсутній'; return; }

        // Обновляем счётчик
        $db->execute("UPDATE attachments SET downloads = downloads + 1 WHERE id = ?", [$file['id']]);

        header('Content-Type: ' . $file['mime']);
        header('Content-Disposition: attachment; filename="' . addslashes($file['original_name']) . '"');
        header('Content-Length: ' . $file['size_bytes']);
        readfile($path);
        exit;
    }
}
