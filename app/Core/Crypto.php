<?php
declare(strict_types=1);
namespace App\Core;

/**
 * Шифрування секретів через sodium (API-ключі, RCON-паролі).
 */
final class Crypto
{
    private static ?string $key = null;

    /**
     * Отримати ключ шифрування.
     */
    private static function key(): string
    {
        if (self::$key === null) {
            $hex = Config::get('crypto_key', '');
            if (!$hex || strlen($hex) !== 64) {
                throw new \RuntimeException('crypto_key не налаштовано або некоректний (потрібно 32 байти hex = 64 символи)');
            }
            self::$key = sodium_hex2bin($hex);
        }
        return self::$key;
    }

    /**
     * Зашифрувати значення.
     */
    public static function encrypt(string $plaintext): string
    {
        if (!function_exists('sodium_crypto_secretbox')) {
            throw new \RuntimeException('Розширення sodium не встановлено');
        }
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = sodium_crypto_secretbox($plaintext, $nonce, self::key());
        return sodium_bin2hex($nonce . $ciphertext);
    }

    /**
     * Розшифрувати значення.
     */
    public static function decrypt(string $encrypted): string
    {
        if (!function_exists('sodium_crypto_secretbox_open')) {
            throw new \RuntimeException('Розширення sodium не встановлено');
        }
        $data = sodium_hex2bin($encrypted);
        $nonceLen = SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;
        if (strlen($data) < $nonceLen) {
            throw new \RuntimeException('Некоректні зашифровані дані');
        }
        $nonce = substr($data, 0, $nonceLen);
        $ciphertext = substr($data, $nonceLen);
        $plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, self::key());
        if ($plaintext === false) {
            throw new \RuntimeException('Не вдалося розшифрувати');
        }
        return $plaintext;
    }

    /**
     * Безпечно перевірити чи sodium доступний.
     */
    public static function isAvailable(): bool
    {
        return function_exists('sodium_crypto_secretbox');
    }
}
