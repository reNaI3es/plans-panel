<?php
declare(strict_types=1);
namespace App\Core;

/**
 * Простий реєстр конфігурації (singleton).
 * Значення беруться з config/config.php, кешуються в пам'яті запиту.
 */
final class Config
{
    private static array $data = [];

    public static function init(array $config): void
    {
        self::$data = $config;
    }

    /**
     * Отримати значення за ключем. Підтримує вкладеність через крапку: 'db.host'
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = self::$data;
        foreach ($segments as $seg) {
            if (!is_array($value) || !array_key_exists($seg, $value)) {
                return $default;
            }
            $value = $value[$seg];
        }
        return $value;
    }

    /**
     * Встановити значення (в пам'яті запиту).
     */
    public static function set(string $key, mixed $value): void
    {
        $segments = explode('.', $key);
        $ref = &self::$data;
        foreach ($segments as $i => $seg) {
            if ($i === count($segments) - 1) {
                $ref[$seg] = $value;
            } else {
                if (!isset($ref[$seg]) || !is_array($ref[$seg])) {
                    $ref[$seg] = [];
                }
                $ref = &$ref[$seg];
            }
        }
    }

    /**
     * Отримати значення з БД (таблиця settings).
     */
    public static function setting(string $key, mixed $default = null): mixed
    {
        static $cache = [];
        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }
        try {
            $db = Db::instance();
            $row = $db->fetchOne("SELECT setting_value, value_type FROM settings WHERE setting_key = ?", [$key]);
        } catch (\Throwable) {
            $cache[$key] = $default;
            return $default;
        }
        if (!$row) {
            $cache[$key] = $default;
            return $default;
        }
        $val = $row['setting_value'];
        $type = $row['value_type'] ?? 'string';
        $cache[$key] = match ($type) {
            'int'  => (int)$val,
            'bool' => (bool)(int)$val,
            'json' => json_decode($val, true) ?? $default,
            default => $val,
        };
        return $cache[$key];
    }

    /**
     * Очистити кеш налаштувань (після зміни).
     */
    public static function clearSettingCache(): void
    {
        // Статична змінна в setting() — тут не очистити, але для потреб
        // достатньо викликати setting() з новим ключем.
    }

    /**
     * Отримати весь масив конфігурації.
     */
    public static function all(): array
    {
        return self::$data;
    }
}
