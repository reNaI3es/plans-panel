<?php
declare(strict_types=1);
/**
 * Глобальні хелпери — url(), asset(), e(), redirect(), flash(), old(), dd().
 */

use App\Core\Config;

/**
 * Побудова URL відносно BASE_PATH.
 * url('/tasks') → /team/tasks (якщо сайт у підпапці /team/)
 */
function url(string $path = '', array $params = []): string {
    $base = BASE_PATH;
    $path = '/' . ltrim($path, '/');
    if ($params) {
        $path .= '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }
    return $base . $path;
}

/**
 * URL до статичного ассету.
 */
function asset(string $path): string {
    return BASE_PATH . '/assets/' . ltrim($path, '/');
}

/**
 * Екранування HTML (запобігання XSS).
 */
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Редирект.
 */
function redirect(string $url, int $code = 302): void {
    http_response_code($code);
    header('Location: ' . $url);
    exit;
}

/**
 * Flash-повідомлення (зберегти або отримати).
 */
function flash(string $key, ?string $value = null): ?string {
    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }
    $val = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $val;
}

/**
 * Значення поля з попереднього POST (для заповнення форм після помилки валідації).
 */
function old(string $key, string $default = ''): string {
    return (string)($_SESSION['_old'][$key] ?? $default);
}

/**
 * Зберегти старі дані форми в сесію.
 */
function old_save(array $data): void {
    $_SESSION['_old'] = $data;
}

/**
 * Очистити старі дані форми.
 */
function old_clear(): void {
    unset($_SESSION['_old']);
}

/**
 * CSRF-токен (з сесії).
 */
function csrf_token(): string {
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

/**
 * CSRF-поле для форми.
 */
function csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

/**
 * Перевірка CSRF-токена з POST.
 */
function csrf_check(): bool {
    $token = $_POST['_csrf'] ?? '';
    $expected = $_SESSION['_csrf'] ?? '';
    if (!$token || !$expected) {
        return false;
    }
    return hash_equals($expected, $token);
}

/**
 * Форматування дати/часу.
 */
function dt(?string $datetime, string $format = 'd.m.Y H:i'): string {
    if (!$datetime) return '—';
    try {
        return (new DateTimeImmutable($datetime))->format($format);
    } catch (Throwable) {
        return '—';
    }
}

/**
 * Відносний час ("5 хв тому", "2 год тому").
 */
function time_ago(?string $datetime): string {
    if (!$datetime) return '—';
    try {
        $diff = time() - (new DateTimeImmutable($datetime))->getTimestamp();
    } catch (Throwable) {
        return '—';
    }
    if ($diff < 60) return 'щойно';
    if ($diff < 3600) return intdiv($diff, 60) . ' хв тому';
    if ($diff < 86400) return intdiv($diff, 3600) . ' год тому';
    if ($diff < 604800) return intdiv($diff, 86400) . ' дн тому';
    return dt($datetime, 'd.m.Y');
}

/**
 * Генерація public_id (12 символів, URL-safe).
 */
function generate_public_id(): string {
    $chars = 'abcdefghijkmnpqrstuvwxyz23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
    $id = '';
    $bytes = random_bytes(12);
    for ($i = 0; $i < 12; $i++) {
        $id .= $chars[ord($bytes[$i]) % strlen($chars)];
    }
    return $id;
}

/**
 * Хеш IP-адреси (для логів, без можливості відновлення).
 */
function ip_hash(?string $ip = null): string {
    $ip = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    $secret = Config::get('ip_hmac_secret', 'default-ip-secret');
    return hash_hmac('sha256', $ip, $secret);
}

/**
 * Отримання IP клієнта.
 */
function client_ip(): string {
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Обрізання рядка.
 */
function str_limit(string $text, int $limit = 100, string $end = '…'): string {
    if (mb_strlen($text) <= $limit) return $text;
    return mb_substr($text, 0, $limit) . $end;
}

/**
 * JSON-декодування з дефолтом.
 */
function json_decode_safe(?string $json, mixed $default = []): mixed {
    if (!$json) return $default;
    $result = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    return $result ?? $default;
}

/**
 * Проста перевірка — чи це AJAX-запит.
 */
function is_ajax(): bool {
    return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
}

/**
 * Отримання user-agent.
 */
function user_agent(): string {
    return substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
}
