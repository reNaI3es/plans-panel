<?php
declare(strict_types=1);
namespace App\Core;

/**
 * Журнал дій та лог входів.
 */
final class Audit
{
    /**
     * Записати дію в аудит.
     */
    public static function log(
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $entityLabel = null,
        ?string $ipHash = null,
        ?string $userAgent = null,
        ?array $before = null,
        ?array $after = null
    ): void {
        try {
            $db = Db::instance();
            $db->execute(
                "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, entity_label, before_data, after_data, ip_hash, user_agent)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    Auth::id(),
                    $action,
                    $entityType,
                    $entityId,
                    $entityLabel,
                    $before ? json_encode($before, JSON_UNESCAPED_UNICODE) : null,
                    $after ? json_encode($after, JSON_UNESCAPED_UNICODE) : null,
                    $ipHash ?? ip_hash(),
                    $userAgent ?? user_agent(),
                ]
            );
        } catch (\Throwable $e) {
            error_log('Audit log failed: ' . $e->getMessage());
        }
    }

    /**
     * Отримати останні записи аудиту.
     */
    public static function recent(int $limit = 50, ?int $userId = null): array
    {
        $db = Db::instance();
        $sql = "SELECT al.*, u.display_name, u.login
                FROM audit_logs al
                LEFT JOIN users u ON u.id = al.user_id";
        $params = [];
        if ($userId) {
            $sql .= " WHERE al.user_id = ?";
            $params[] = $userId;
        }
        $sql .= " ORDER BY al.created_at DESC LIMIT ?";
        $params[] = $limit;
        return $db->fetchAll($sql, $params);
    }
}
