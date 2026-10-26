<?php
declare(strict_types=1);
namespace App\Services;

use App\Core\{Db, Config};

/**
 * Cron-сервіс — обробка фонових задач.
 */
class CronService
{
    /**
     * Запустити всі фонові задачі.
     */
    public function runAll(): array
    {
        $results = [];
        $results['monitor'] = $this->runMonitor();
        $results['reminders'] = $this->runReminders();
        $results['notifications'] = $this->runNotifications();
        $results['recurrence'] = $this->runRecurrence();
        $results['cleanup'] = $this->runCleanup();
        return $results;
    }

    /**
     * Моніторинг серверів.
     */
    public function runMonitor(): string
    {
        try {
            $monitor = new MonitorService();
            $results = $monitor->checkAll();
            return 'Перевірено ' . count($results) . ' серверів';
        } catch (\Throwable $e) {
            return 'Помилка: ' . $e->getMessage();
        }
    }

    /**
     * Обробка нагадувань.
     */
    public function runReminders(): string
    {
        $db = Db::instance();
        $reminders = $db->fetchAll(
            "SELECT r.*, t.code, t.title, u.display_name
             FROM reminders r
             JOIN tasks t ON t.id = r.task_id
             JOIN users u ON u.id = r.user_id
             WHERE r.remind_at <= NOW() AND r.is_sent = 0
             LIMIT 100"
        );

        $notify = new NotifyService();
        $sent = 0;
        foreach ($reminders as $r) {
            $notify->notify(
                (int)$r['user_id'],
                'reminder',
                "Нагадування: {$r['title']}",
                "Завдання {$r['code']} нагадує про себе.",
                '/tasks/' . ($r['task_id'] ?? ''),
                'warning'
            );
            $db->execute("UPDATE reminders SET is_sent = 1 WHERE id = ?", [$r['id']]);
            $sent++;
        }
        return "Нагадувань: {$sent}";
    }

    /**
     * Обробка черги сповіщень.
     */
    public function runNotifications(): string
    {
        $notify = new NotifyService();
        $sent = $notify->processQueue();
        return "Відправлено: {$sent}";
    }

    /**
     * Генерація регулярних завдань.
     */
    public function runRecurrence(): string
    {
        $db = Db::instance();
        $recurrences = $db->fetchAll(
            "SELECT * FROM task_recurrence WHERE is_active = 1"
        );

        $created = 0;
        foreach ($recurrences as $rec) {
            if ($this->shouldGenerate($rec)) {
                $this->generateTask($rec);
                $created++;
            }
        }
        return "Створено регулярних: {$created}";
    }

    /**
     * Очищення старих даних.
     */
    public function runCleanup(): string
    {
        $db = Db::instance();
        // Видаляємо старі rate_limits
        $db->execute("DELETE FROM rate_limits WHERE window_start < DATE_SUB(NOW(), INTERVAL 1 DAY)");
        // Видаляємо старі server_stats (залишаємо 7 днів)
        $db->execute("DELETE FROM server_stats WHERE checked_at < DATE_SUB(NOW(), INTERVAL 7 DAY)");
        // Видаляємо використані password_resets
        $db->execute("DELETE FROM password_resets WHERE used_at IS NOT NULL OR expires_at < NOW()");
        return 'Очищення завершено';
    }

    private function shouldGenerate(array $rec): bool
    {
        $now = new \DateTimeImmutable();
        $periodKey = $now->format('Y-m-d');

        // Перевіряємо чи вже створено сьогодні
        if ($rec['last_generated_key'] === $periodKey) {
            return false;
        }

        // Перевіряємо частоту
        return match ($rec['freq']) {
            'daily' => true,
            'weekly' => $now->format('N') == 1, // Понеділок
            'monthly' => $now->format('j') == 1, // Перше число
            default => false,
        };
    }

    private function generateTask(array $rec): void
    {
        $db = Db::instance();
        $now = new \DateTimeImmutable();
        $periodKey = $now->format('Y-m-d');

        // Генеруємо код
        $lastCode = (int)$db->fetchColumn("SELECT MAX(CAST(SUBSTRING(code, 4) AS UNSIGNED)) FROM tasks");
        $code = 'RN-' . str_pad((string)($lastCode + 1), 6, '0', STR_PAD_LEFT);

        // Створюємо завдання
        $db->execute(
            "INSERT INTO tasks (public_id, code, title, description, type, priority, status, assign_mode, category_id, created_by, recurrence_id, period_key, due_at)
             VALUES (?, ?, ?, ?, ?, ?, 'new', ?, ?, ?, ?, ?, ?)",
            [
                generate_public_id(),
                $code,
                $rec['title'],
                $rec['description'],
                $rec['type'],
                $rec['priority'],
                $rec['assign_mode'],
                $rec['category_id'],
                $rec['created_by'],
                $rec['id'],
                $periodKey,
                $now->setTime((int)$rec['due_hour'], 0, 0)->format('Y-m-d H:i:s'),
            ]
        );
        $taskId = (int)$db->lastInsertId();

        // Призначаємо виконавців
        $targetIds = json_decode($rec['target_ids'] ?? '[]', true) ?: [];
        if ($rec['target_kind'] === 'category_all' && $rec['category_id']) {
            $members = $db->fetchAll("SELECT user_id FROM category_user WHERE category_id = ?", [$rec['category_id']]);
            $targetIds = array_column($members, 'user_id');
        } elseif ($rec['target_kind'] === 'category_leads' && $rec['category_id']) {
            $leads = $db->fetchAll("SELECT user_id FROM category_user WHERE category_id = ? AND role_in_category = 'lead'", [$rec['category_id']]);
            $targetIds = array_column($leads, 'user_id');
        }

        foreach ($targetIds as $uid) {
            $db->execute(
                "INSERT INTO task_assignments (task_id, user_id, due_at) VALUES (?, ?, ?)",
                [$taskId, (int)$uid, $now->setTime((int)$rec['due_hour'], 0, 0)->format('Y-m-d H:i:s')]
            );
        }

        // Оновлюємо last_generated_key
        $db->execute("UPDATE task_recurrence SET last_generated_key = ? WHERE id = ?", [$periodKey, $rec['id']]);
    }
}
