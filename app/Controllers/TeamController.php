<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\{Auth, View, Db, Acl};

class TeamController
{
    public function index(): void
    {
        Acl::require('team.view');
        $db = Db::instance();
        $members = $db->fetchAll(
            "SELECT u.*, r.title as role_title,
                    (SELECT COUNT(*) FROM task_assignments ta JOIN tasks t ON t.id = ta.task_id
                     WHERE ta.user_id = u.id AND t.status NOT IN ('done','cancelled') AND t.deleted_at IS NULL) as active_tasks,
                    (SELECT COUNT(*) FROM task_assignments ta JOIN tasks t ON t.id = ta.task_id
                     WHERE ta.user_id = u.id AND t.status = 'done' AND t.completed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as done_30d
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE u.status = 'active'
             ORDER BY r.level DESC, u.display_name"
        );
        View::render('team/index', ['pageTitle' => 'Команда', 'members' => $members]);
    }

    public function matrix(): void
    {
        Acl::require('team.matrix');
        $db = Db::instance();

        $members = $db->fetchAll(
            "SELECT u.id, u.public_id, u.display_name, u.avatar_initials, u.last_activity_at,
                    (SELECT COUNT(*) FROM task_assignments ta JOIN tasks t ON t.id = ta.task_id
                     WHERE ta.user_id = u.id AND t.status NOT IN ('done','cancelled') AND t.deleted_at IS NULL) as active_tasks,
                    (SELECT COUNT(*) FROM task_assignments ta JOIN tasks t ON t.id = ta.task_id
                     WHERE ta.user_id = u.id AND t.status NOT IN ('done','cancelled') AND t.deleted_at IS NULL AND t.due_at < NOW()) as overdue_tasks,
                    (SELECT COUNT(*) FROM task_assignments ta JOIN tasks t ON t.id = ta.task_id
                     WHERE ta.user_id = u.id AND t.status = 'done' AND t.completed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as done_30d
             FROM users u WHERE u.status = 'active' ORDER BY u.display_name"
        );

        $categories = $db->fetchAll(
            "SELECT * FROM categories WHERE is_active = 1 AND visibility = 'team' ORDER BY sort_order"
        );

        // Загружаем связи человек × категория
        $matrix = [];
        foreach ($members as $m) {
            $matrix[$m['id']] = [];
        }
        if ($members && $categories) {
            $rows = $db->fetchAll(
                "SELECT cu.user_id, cu.category_id, cu.role_in_category, cu.workload
                 FROM category_user cu"
            );
            foreach ($rows as $row) {
                $matrix[$row['user_id']][$row['category_id']] = $row;
            }
        }

        View::render('team/matrix', [
            'pageTitle' => 'Матриця відповідальності',
            'members' => $members,
            'categories' => $categories,
            'matrix' => $matrix,
        ]);
    }

    public function show(string $id): void
    {
        Acl::require('team.view');
        $db = Db::instance();
        $member = $db->fetchOne(
            "SELECT u.*, r.title as role_title, r.slug as role_slug
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE u.public_id = ? AND u.status = 'active'",
            [$id]
        );
        if (!$member) { http_response_code(404); View::render('errors/404', [], 'layouts/error'); return; }

        $categories = $db->fetchAll(
            "SELECT c.*, cu.role_in_category, cu.responsibilities, cu.workload
             FROM category_user cu JOIN categories c ON c.id = cu.category_id
             WHERE cu.user_id = ? AND c.is_active = 1
             ORDER BY c.sort_order",
            [$member['id']]
        );

        $tasks = $db->fetchAll(
            "SELECT t.public_id, t.code, t.title, t.status, t.priority, t.due_at, t.category_id
             FROM task_assignments ta JOIN tasks t ON t.id = ta.task_id
             WHERE ta.user_id = ? AND t.deleted_at IS NULL
             ORDER BY t.priority DESC, t.due_at ASC LIMIT 50",
            [$member['id']]
        );

        View::render('team/show', ['pageTitle' => $member['display_name'], 'member' => $member, 'categories' => $categories, 'tasks' => $tasks]);
    }
}
