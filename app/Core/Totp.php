<?php
declare(strict_types=1);
namespace App\Core;

/**
 * TOTP (RFC 6238) — власна реалізація без зовнішніх бібліотек.
 * SHA1, 30 сек, 6 цифр, вікно ±1.
 */
final class Totp
{
    private const DIGITS = 6;
    private const PERIOD = 30;
    private const ALGORITHM = 'sha1';

    /**
     * Згенерувати випадковий секрет (20 байт, base32).
     */
    public static function generateSecret(): string
    {
        $bytes = random_bytes(20);
        return self::base32Encode($bytes);
    }

    /**
     * Обчислити TOTP-код для заданого часу.
     */
    public static function generateCode(string $secret, ?int $time = null): string
    {
        $time = $time ?? time();
        $counter = intdiv($time, self::PERIOD);
        $secretBytes = self::base32Decode($secret);

        // HMAC-SHA1
        $counterBytes = pack('N*', 0) . pack('N*', $counter);
        $hash = hash_hmac(self::ALGORITHM, $counterBytes, $secretBytes, true);

        // Dynamic truncation
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $code = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        ) % (10 ** self::DIGITS);

        return str_pad((string)$code, self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * Перевірити код з вікном ±1.
     */
    public static function verify(string $secret, string $code): bool
    {
        $time = time();
        for ($offset = -1; $offset <= 1; $offset++) {
            $expected = self::generateCode($secret, $time + ($offset * self::PERIOD));
            if (hash_equals($expected, $code)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Створити provisioning URI для QR-коду.
     */
    public static function getProvisioningUri(string $secret, string $account, string $issuer = 'reNaI3es'): string
    {
        $params = [
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => strtoupper(self::ALGORITHM),
            'digits' => self::DIGITS,
            'period' => self::PERIOD,
        ];
        $label = rawurlencode($issuer) . ':' . rawurlencode($account);
        return 'otpauth://totp/' . $label . '?' . http_build_query($params);
    }

    /**
     * Згенерувати резервні коди (8 штук).
     * @return array Масив хешів кодів
     */
    public static function generateBackupCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $code = strtoupper(bin2hex(random_bytes(4)));
            $codes[] = [
                'plain' => $code,
                'hash'  => password_hash($code, PASSWORD_ARGON2ID),
            ];
        }
        return $codes;
    }

    /**
     * Base32 encode.
     */
    private static function base32Encode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binary = '';
        foreach (str_split($data) as $char) {
            $binary .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }
        $result = '';
        foreach (str_split($binary, 5) as $chunk) {
            $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            $result .= $alphabet[bindec($chunk)];
        }
        return $result;
    }

    /**
     * Base32 decode.
     */
    private static function base32Decode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $data = strtoupper(rtrim($data, '='));
        $binary = '';
        foreach (str_split($data) as $char) {
            $pos = strpos($alphabet, $char);
            if ($pos === false) continue;
            $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }
        $result = '';
        foreach (str_split($binary, 8) as $byte) {
            if (strlen($byte) < 8) break;
            $result .= chr(bindec($byte));
        }
        return $result;
    }
}
