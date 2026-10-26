<?php
declare(strict_types=1);
/**
 * CLI-вхід для cron: php cron/run.php
 * Виклик: */5 * * * * php /path/to/cron/run.php >> /var/log/renai3es-cron.log 2>&1
 */

// Завантаження ядра
require_once dirname(__DIR__) . '/app/bootstrap.php';

// Перевірка CLI
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo 'CLI only';
    exit;
}

// Перевірка ключа (якщо викликається по HTTP через CronController)
if (php_sapi_name() === 'cli') {
    // CLI — запускаємо все
} else {
    // HTTP — перевіряємо секретний ключ
    $key = $_GET['key'] ?? '';
    $expected = \App\Core\Config::get('cron_key', '');
    if (!$expected || !hash_equals($expected, $key)) {
        http_response_code(403);
        echo 'Invalid cron key';
        exit;
    }
}

echo "[" . date('Y-m-d H:i:s') . "] Початок cron-задач\n";

$cron = new \App\Services\CronService();
$results = $cron->runAll();

foreach ($results as $name => $result) {
    echo "  [{$name}] {$result}\n";
}

echo "[" . date('Y-m-d H:i:s') . "] Завершено\n";
