<?php
declare(strict_types=1);
namespace App\Core;

/**
 * Система прав доступу: ролі + гранулярні permissions.
 * Перевірка — одна точка: Acl::can('tasks.assign', $context).
 */
final class Acl
{
    /** @var array Кеш прав поточного користувача */
    private static array $cache = [];

    /**
     * Перевірити чи поточний користувач має право.
     * @param string $permission Permission key (напр. 'tasks.assign')
     * @param array $context Контекст (category_id, task_id, user_id, etc.)
     */
    public static function can(string $permission, array $context = []): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        // Owner має все
        if ($user['role_slug'] === 'owner') return true;

        // Завантажуємо права користувача
        $perms = self::getUserPermissions((int)$user['id']);

        // Базова перевірка
        if (!in_array($permission, $perms, true)) {
            return false;
        }

        // Контекстна перевірка для manager
        if ($user['role_slug'] === 'manager' && !empty($context['category_id'])) {
            return self::managerCanAccessCategory((int)$user['id'], (int)$context['category_id']);
        }

        // Перевірка "власник запису" для edit.own
        if ($permission === 'tasks.edit.own' && !empty($context['task_id'])) {
            return self::isTaskOwner((int)$user['id'], (int)$context['task_id']);
        }

        return true;
    }

    /**
     * Перевірити і кинути 403 якщо немає права.
     */
    public static function require(string $permission, array $context = []): void
    {
        if (!self::can($permission, $context)) {
            http_response_code(403);
            View::render('errors/403', ['permission' => $permission], 'layouts/error');
            exit;
        }
    }

    /**
     * Отримати всі права користувача.
     */
    public static function getUserPermissions(int $userId): array
    {
        if (isset(self::$cache[$userId])) {
            return self::$cache[$userId];
        }

        $db = Db::instance();
        $rows = $db->fetchAll(
            "SELECT p.slug
             FROM role_permissions rp
             JOIN permissions p ON p.id = rp.permission_id
             JOIN users u ON u.role_id = rp.role_id
             WHERE u.id = ?",
            [$userId]
        );

        $perms = array_column($rows, 'slug');
        self::$cache[$userId] = $perms;
        return $perms;
    }

    /**
     * Перевірити чи manager має доступ до категорії.
     */
    private static function managerCanAccessCategory(int $userId, int $categoryId): bool
    {
        $db = Db::instance();
        $row = $db->fetchOne(
            "SELECT id FROM category_user WHERE user_id = ? AND category_id = ? AND role_in_category IN ('lead','member')",
            [$userId, $categoryId]
        );
        return $row !== null;
    }

    /**
     * Перевірити чи користувач є власником/виконавцем завдання.
     */
    private static function isTaskOwner(int $userId, int $taskId): bool
    {
        $db = Db::instance();
        $row = $db->fetchOne(
            "SELECT id FROM task_assignments WHERE task_id = ? AND user_id = ?",
            [$taskId, $userId]
        );
        if ($row) return true;

        // Або він автор завдання
        $task = $db->fetchOne("SELECT created_by FROM tasks WHERE id = ?", [$taskId]);
        return $task && (int)$task['created_by'] === $userId;
    }

    /**
     * Скинути кеш прав.
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }

    /**
     * Отримати всі ролі.
     */
    public static function getRoles(): array
    {
        $db = Db::instance();
        return $db->fetchAll("SELECT * FROM roles ORDER BY level DESC");
    }

    /**
     * Отримати всі permissions згруповані.
     */
    public static function getPermissionsGrouped(): array
    {
        $db = Db::instance();
        $rows = $db->fetchAll("SELECT * FROM permissions ORDER BY group_name, slug");
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['group_name']][] = $row;
        }
        return $grouped;
    }
}
