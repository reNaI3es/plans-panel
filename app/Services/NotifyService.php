<?php
declare(strict_types=1);
namespace App\Services;

use App\Core\{Db, Config, Auth};

/**
 * Сервіс сповіщень: in-app + Telegram + Discord.
 */
class NotifyService
{
    /**
     * Створити in-app сповіщення + додати в чергу Telegram/Discord.
     */
    public function notify(int $userId, string $type, string $title, string $body = '', string $url = '', string $severity = 'info'): void
    {
        $db = Db::instance();

        // Перевірка "тихі години" (critical ігнорує)
        if ($severity !== 'critical' && $this->isQuietTime($userId)) {
            // Відкладаємо на кінець тихої години
            $scheduledAt = $this->getQuietEnd($userId);
        } else {
            $scheduledAt = date('Y-m-d H:i:s');
        }

        // In-app
        $db->execute(
            "INSERT INTO notifications (user_id, type, title, body, url, severity) VALUES (?, ?, ?, ?, ?, ?)",
            [$userId, $type, $title, $body, $url, $severity]
        );

        // Telegram
        $user = $db->fetchOne("SELECT telegram_id FROM users WHERE id = ?", [$userId]);
        if ($user && $user['telegram_id'] && Config::setting('telegram_enabled')) {
            $db->execute(
                "INSERT INTO notification_queue (user_id, channel, payload, scheduled_at) VALUES (?, 'telegram', ?, ?)",
                [$userId, json_encode(['chat_id' => $user['telegram_id'], 'text' => "🔔 {$title}\n\n{$body}"], JSON_UNESCAPED_UNICODE), $scheduledAt]
            );
        }

        // Discord
        if (Config::setting('discord_enabled')) {
            $db->execute(
                "INSERT INTO notification_queue (user_id, channel, payload, scheduled_at) VALUES (?, 'discord', ?, ?)",
                [$userId, json_encode(['content' => "<@{$userId}> **{$title}**\n{$body}"], JSON_UNESCAPED_UNICODE), $scheduledAt]
            );
        }
    }

    /**
     * Обробити чергу сповіщень (викликається кроном).
     */
    public function processQueue(int $limit = 50): int
    {
        $db = Db::instance();
        $items = $db->fetchAll(
            "SELECT * FROM notification_queue WHERE status = 'pending' AND scheduled_at <= NOW() ORDER BY scheduled_at LIMIT ?",
            [$limit]
        );

        $sent = 0;
        foreach ($items as $item) {
            $success = match ($item['channel']) {
                'telegram' => $this->sendTelegram($item),
                'discord'  => $this->sendDiscord($item),
                default    => false,
            };

            if ($success) {
                $db->execute("UPDATE notification_queue SET status = 'sent', sent_at = NOW() WHERE id = ?", [$item['id']]);
                $sent++;
            } else {
                $attempts = (int)$item['attempts'] + 1;
                $newStatus = $attempts >= 3 ? 'failed' : 'pending';
                $db->execute(
                    "UPDATE notification_queue SET status = ?, attempts = ? WHERE id = ?",
                    [$newStatus, $attempts, $item['id']]
                );
            }
        }
        return $sent;
    }

    private function sendTelegram(array $item): bool
    {
        $token = Config::setting('telegram_bot_token');
        if (!$token) return false;

        $payload = json_decode($item['payload'], true);
        $url = "https://api.telegram.org/bot{$token}/sendMessage";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POSTFIELDS => http_build_query([
                'chat_id' => $payload['chat_id'],
                'text' => $payload['text'],
                'parse_mode' => 'HTML',
            ]),
        ]);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        return $response !== false && !$error;
    }

    private function sendDiscord(array $item): bool
    {
        $webhook = Config::setting('discord_webhook_url');
        if (!$webhook) return false;

        $payload = json_decode($item['payload'], true);
        $ch = curl_init($webhook);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        return $response !== false && !$error;
    }

    private function isQuietTime(int $userId): bool
    {
        $db = Db::instance();
        $user = $db->fetchOne("SELECT quiet_from, quiet_to FROM users WHERE id = ?", [$userId]);
        if (!$user || !$user['quiet_from'] || !$user['quiet_to']) return false;

        $now = date('H:i:s');
        $from = $user['quiet_from'];
        $to = $user['quiet_to'];

        if ($from <= $to) {
            return $now >= $from && $now <= $to;
        }
        // Через північ
        return $now >= $from || $now <= $to;
    }

    private function getQuietEnd(int $userId): string
    {
        $db = Db::instance();
        $user = $db->fetchOne("SELECT quiet_to FROM users WHERE id = ?", [$userId]);
        $to = $user['quiet_to'] ?? '09:00:00';
        return date('Y-m-d') . ' ' . $to;
    }
}
