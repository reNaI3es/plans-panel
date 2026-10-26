<?php
declare(strict_types=1);
/**
 * Точка входу ядра: завантажує конфіг, реєструє автозавантажувач,
 * налаштовує сесію, встановлює заголовки безпеки, визначає BASE_PATH.
 */

// --- Часовий пояс ---
date_default_timezone_set('Europe/Kyiv');

// --- Помилки ---
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// --- Конфігурація ---
$configFile = dirname(__DIR__, 1) . '/config/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    echo 'Файл config/config.php не знайдено. Скопіюй config.example.php → config.php і заповни.';
    exit(1);
}
$cfg = require $configFile;
if (!is_array($cfg)) {
    http_response_code(500);
    echo 'config.php має повертати масив.';
    exit(1);
}

// --- Визначення BASE_PATH (працює і в підпапці, і на піддомені) ---
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$scriptDir = rtrim($scriptDir, '/');
define('BASE_PATH', $scriptDir);

// --- Часовий пояс з конфігу ---
if (!empty($cfg['timezone'])) {
    date_default_timezone_set($cfg['timezone']);
}

// --- Автозавантажувач (PSR-4-подібний для app/) ---
spl_autoload_register(function (string $class): void {
    // Простір імен: App\Core\Router → app/Core/Router.php
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = dirname(__DIR__, 1) . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

// --- Хелпери (глобальні функції) ---
require_once dirname(__DIR__, 1) . '/app/Core/helpers.php';

// --- Ініціалізуємо глобальний об'єкт конфігу ---
\App\Core\Config::init($cfg);

// --- Заголовки безпеки ---
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
if ($isHttps) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}
// CSP — дозволити three.js з CDN
$csp = "default-src 'self'; "
     . "script-src 'self' https://cdnjs.cloudflare.com; "
     . "style-src 'self' 'unsafe-inline'; "
     . "img-src 'self' data:; "
     . "font-src 'self'; "
     . "connect-src 'self'; "
     . "frame-ancestors 'self'; "
     . "base-uri 'self'; "
     . "form-action 'self'";
header('Content-Security-Policy: ' . $csp);

// --- Сесія ---
$sessionName = $cfg['session_name'] ?? 'rn3ses';
session_name($sessionName);
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => BASE_PATH ?: '/',
    'secure'   => $isHttps,
    'httponly'  => true,
    'samesite'  => 'Strict',
]);

// --- Перевірка встановлення ---
$installedLock = ($cfg['storage_path'] ?? dirname(__DIR__, 1) . '/storage') . '/installed.lock';
define('APP_INSTALLED', is_file($installedLock));
define('APP_IS_HTTPS', $isHttps);
