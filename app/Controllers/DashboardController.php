<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\{Auth, View, Db, Acl};

/**
 * Контролер дашборду — головна сторінка для Owner та Member.
 */
class DashboardController
{
    public function index(): void
    {
        $user = Auth::user();
        if (!$user) {
            redirect(url('/login'));
        }

        $db = Db::instance();

        if ($user['role_slug'] === 'owner' || $user['role_slug'] === 'manager') {
            $this->renderOwnerDashboard($user, $db);
        } else {
            $this->renderMemberDashboard($user, $db);
        }
    }

    private function renderOwnerDashboard(array $user, Db $db): void
    {
        // Статистика
        $stats = [
            'active' => (int)$db->fetchColumn(
                "SELECT COUNT(*) FROM tasks WHERE status NOT IN ('done','cancelled') AND deleted_at IS NULL"
            ),
            'overdue' => (int)$db->fetchColumn(
                "SELECT COUNT(*) FROM tasks WHERE status NOT IN ('done','cancelled') AND deleted_at IS NULL AND due_at < NOW()"
            ),
            'review' => (int)$db->fetchColumn(
                "SELECT COUNT(*) FROM tasks WHERE status = 'review' AND deleted_at IS NULL"
            ),
            'done_week' => (int)$db->fetchColumn(
                "SELECT COUNT(*) FROM tasks WHERE status = 'done' AND completed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
            ),
        ];

        // Потребує уваги
        $needsAttention = [];
        if ($stats['overdue'] > 0) {
            $needsAttention[] = ['text' => "Прострочено завдань: {$stats['overdue']}", 'type' => 'Прострочено', 'severity' => 'danger'];
        }
        if ($stats['review'] > 0) {
            $needsAttention[] = ['text' => "На перевірці: {$stats['review']}", 'type' => 'Перевірка', 'severity' => 'warning'];
        }

        // Команда
        $team = $db->fetchAll(
            "SELECT u.id, u.public_id, u.display_name, u.avatar_initials, u.last_activity_at,
                    r.title as role_title,
                    (SELECT COUNT(*) FROM task_assignments ta JOIN tasks t ON t.id = ta.task_id
                     WHERE ta.user_id = u.id AND t.status NOT IN ('done','cancelled') AND t.deleted_at IS NULL) as active_tasks,
                    (SELECT COALESCE(MAX(cu.workload), 0) FROM category_user cu WHERE cu.user_id = u.id) as workload
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.status = 'active'
             ORDER BY u.last_activity_at DESC
             LIMIT 20"
        );

        // Останні події
        $recentActivity = $db->fetchAll(
            "SELECT al.*, u.display_name
             FROM audit_logs al
             LEFT JOIN users u ON u.id = al.user_id
             ORDER BY al.created_at DESC
             LIMIT 10"
        );

        View::render('dashboard/owner', [
            'pageTitle' => 'Головна',
            'stats' => $stats,
            'needsAttention' => $needsAttention,
            'team' => $team,
            'recentActivity' => $recentActivity,
        ]);
    }

    private function renderMemberDashboard(array $user, Db $db): void
    {
        $userId = (int)$user['id'];

        // Термінове (прострочено + сьогодні/24 год)
        $urgent = $db->fetchAll(
            "SELECT t.public_id, t.code, t.title, t.due_at, t.priority
             FROM task_assignments ta
             JOIN tasks t ON t.id = ta.task_id
             WHERE ta.user_id = ?
               AND t.status NOT IN ('done','cancelled')
               AND t.deleted_at IS NULL
               AND (t.due_at < NOW() OR t.due_at <= DATE_ADD(NOW(), INTERVAL 24 HOUR))
             ORDER BY t.due_at ASC
             LIMIT 10",
            [$userId]
        );

        // Мої категорії
        $myCategories = $db->fetchAll(
            "SELECT c.id, c.title, c.icon, c.duty_text, cu.role_in_category,
                    (SELECT COUNT(*) FROM task_assignments ta
                     JOIN tasks t ON t.id = ta.task_id
                     WHERE ta.user_id = ? AND t.category_id = c.id
                       AND t.status NOT IN ('done','cancelled') AND t.deleted_at IS NULL) as active_count
             FROM category_user cu
             JOIN categories c ON c.id = cu.category_id
             WHERE cu.user_id = ? AND c.is_active = 1
             ORDER BY c.sort_order",
            [$userId, $userId]
        );

        // Мої завдання
        $myTasks = $db->fetchAll(
            "SELECT t.public_id, t.code, t.title, t.status, t.priority, t.due_at
             FROM task_assignments ta
             JOIN tasks t ON t.id = ta.task_id
             WHERE ta.user_id = ?
               AND t.status NOT IN ('done','cancelled')
               AND t.deleted_at IS NULL
             ORDER BY t.priority DESC, t.due_at ASC
             LIMIT 10",
            [$userId]
        );

        View::render('dashboard/member', [
            'pageTitle' => 'Головна',
            'urgent' => $urgent,
            'myCategories' => $myCategories,
            'myTasks' => $myTasks,
        ]);
    }
}
