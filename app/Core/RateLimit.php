<?php
declare(strict_types=1);
namespace App\Core;

/**
 * Rate limit на основі БД (таблиця rate_limits).
 */
final class RateLimit
{
    /**
     * Перевірити чи перевищено ліміт.
     * @param string $bucket Ідентифікатор (напр. 'login:ip:abc123')
     * @param int $maxHits Максимальна кількість запитів
     * @param int $windowSec Вікно в секундах
     * @return bool true якщо ліміт перевищено
     */
    public static function check(string $bucket, int $maxHits, int $windowSec): bool
    {
        $db = Db::instance();
        $now = date('Y-m-d H:i:s');
        $windowStart = date('Y-m-d H:i:s', time() - $windowSec);

        // Очищаємо старі записи
        $db->execute("DELETE FROM rate_limits WHERE window_start < ?", [$windowStart]);

        // Перевіряємо поточний bucket
        $row = $db->fetchOne(
            "SELECT hits, window_start FROM rate_limits WHERE bucket = ?",
            [$bucket]
        );

        if (!$row) {
            // Перший запит
            $db->execute(
                "INSERT INTO rate_limits (bucket, hits, window_start) VALUES (?, 1, ?)",
                [$bucket, $now]
            );
            return false;
        }

        $windowStartTime = strtotime($row['window_start']);
        if (time() - $windowStartTime > $windowSec) {
            // Вікно закінчилося, скидаємо
            $db->execute(
                "UPDATE rate_limits SET hits = 1, window_start = ? WHERE bucket = ?",
                [$now, $bucket]
            );
            return false;
        }

        if ((int)$row['hits'] >= $maxHits) {
            return true; // Ліміт перевищено
        }

        // Збільшуємо лічильник
        $db->execute(
            "UPDATE rate_limits SET hits = hits + 1 WHERE bucket = ?",
            [$bucket]
        );
        return false;
    }

    /**
     * Скинути ліміт для bucket.
     */
    public static function reset(string $bucket): void
    {
        $db = Db::instance();
        $db->execute("DELETE FROM rate_limits WHERE bucket = ?", [$bucket]);
    }
}
