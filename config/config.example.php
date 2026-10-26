<?php
declare(strict_types=1);
/**
 * Приклад конфігурації. Скопіюй у config/config.php, заповни свої дані, вистав права 0600.
 * Цей файл НЕ повинен бути доступний через веб.
 */
return [
    // === Загальні ===
    'app_name'      => 'reNaI3es',
    'app_url'       => '', // порожнє = авто-визначення
    'timezone'      => 'Europe/Kyiv',
    'debug'         => false,

    // === База даних ===
    'db' => [
        'host'    => '127.0.0.1',
        'port'    => 3306,
        'name'    => 'renai3es',
        'user'    => 'renai3es',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],

    // === Безпека ===
    'crypto_key'        => '', // 32 байти hex (sodium_crypto_secretbox_keygen), для шифрування секретів
    'session_name'      => 'rn3ses',
    'session_idle'      => 3600,   // 60 хв
    'session_absolute'  => 28800,  // 8 год
    'cookie_secure'     => true,
    'cookie_samesite'   => 'Strict',
    'ip_hmac_secret'    => '', // окремий секрет для хешування IP в логах

    // === Шляхи ===
    'storage_path' => dirname(__DIR__) . '/storage',
    'views_path'   => dirname(__DIR__) . '/views',

    // === Сповіщення ===
    'telegram_bot_token' => '',
    'discord_webhook'    => '',

    // === ШІ ===
    'ai_base_url' => 'https://dashscope-intl.aliyuncs.com/compatible-mode/v1',
    'ai_model'    => 'qwen-plus',
    'ai_api_key'  => '',
];
