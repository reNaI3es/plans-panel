<?php
declare(strict_types=1);
/**
 * CLI-міграція: php database/migrate.php
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo 'CLI only';
    exit;
}

require_once dirname(__DIR__) . '/app/bootstrap.php';

$db = \App\Core\Db::instance();

echo "Міграція бази даних...\n";

// Перевіряємо чи вже мігрували
$exists = $db->fetchColumn("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ? AND table_name = 'roles'", [
    \App\Core\Config::get('db.name')
]);

if ($exists > 0) {
    echo "Таблиці вже існують. Пропускаємо schema.sql.\n";
} else {
    echo "Виконуємо schema.sql...\n";
    $schema = file_get_contents(__DIR__ . '/schema.sql');
    $db->pdo()->exec($schema);
    echo "✅ Схема створена.\n";
}

// Перевіряємо чи вже сіяли
$seeded = $db->fetchColumn("SELECT COUNT(*) FROM roles");
if ($seeded > 0) {
    echo "Дані вже є. Пропускаємо seed.sql.\n";
} else {
    echo "Виконуємо seed.sql...\n";
    $seed = file_get_contents(__DIR__ . '/seed.sql');
    $db->pdo()->exec($seed);
    echo "✅ Початкові дані завантажено.\n";
}

echo "\nГотово! Тепер запустіть install.php для створення облікового запису Owner.\n";
