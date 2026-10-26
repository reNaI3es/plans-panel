<?php
declare(strict_types=1);
namespace App\Core;

/**
 * CSRF-захист: генерація токенів, перевірка, Origin/Referer.
 */
final class Csrf
{
    /**
     * Middleware: перевіряє CSRF для POST/PUT/DELETE.
     */
    public static function handle(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        if (!in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            return;
        }

        // Перевірка Origin/Referer
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $host = $_SERVER['HTTP_HOST'] ?? '';

        if ($origin) {
            $originHost = parse_url($origin, PHP_URL_HOST);
            if ($originHost !== $host) {
                http_response_code(403);
                View::render('errors/403', ['message' => 'Невірний Origin'], 'layouts/error');
                exit;
            }
        }

        // Перевірка токена
        if (!csrf_check()) {
            http_response_code(403);
            View::render('errors/403', ['message' => 'Недійсний CSRF-токен'], 'layouts/error');
            exit;
        }
    }
}
