<?php
declare(strict_types=1);
namespace App\Core;

/**
 * Опціональний mailer (mail()). Якщо не налаштовано — не падає.
 */
final class Mailer
{
    /**
     * Відправити email.
     * @return bool true якщо відправлено
     */
    public static function send(string $to, string $subject, string $body): bool
    {
        if (!function_exists('mail')) {
            error_log('Mailer: функція mail() недоступна');
            return false;
        }

        $headers = [
            'From: noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'),
            'Content-Type: text/plain; charset=UTF-8',
            'X-Mailer: reNaI3es/1.0',
        ];

        try {
            return @mail($to, $subject, $body, implode("\r\n", $headers));
        } catch (\Throwable $e) {
            error_log('Mailer error: ' . $e->getMessage());
            return false;
        }
    }
}
