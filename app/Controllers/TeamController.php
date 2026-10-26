<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\{Auth, View, Db, Acl, Audit};

class TeamController
{
    public function index(): void
    {
        Acl::require('team.view');
        $db = Db::instance();
        $members = $db->fetchAll(
            "SELECT u.*, r.title as role_title,
                    (SELECT COUNT(*) FROM task_assignments ta JOIN tasks t ON t.id = ta.task_id WHERE ta.user_id = u.id AND t.status NOT IN ('done','cancelled') AND t.deleted_at IS NULL) as active_tasks,
                    (SELECT COUNT(*) FROM task_assignments ta JOIN tasks t ON t.id = ta.task_id WHERE ta.user_id = u.id AND t.status = 'done' AND t.completed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as done_30d
             FROM users u JOIN roles r ON r.id = u.role_id WHERE u.status = 'active' ORDER BY r.level DESC, u.display_name"
        );
        View::render('team/index', ['pageTitle' => 'Команда', 'members' => $members]);
    }

    public function matrix(): void
    {
        Acl::require('team.matrix');
        $db = Db::instance();
        $members = $db->fetchAll(
            "SELECT u.id, u.public_id, u.display_name, u.avatar_initials, u.last_activity_at,
                    (SELECT COUNT(*) FROM task_assignments ta JOIN tasks t ON t.id = ta.task_id WHERE ta.user_id = u.id AND t.status NOT IN ('done','cancelled') AND t.deleted_at IS NULL) as active_tasks,
                    (SELECT COUNT(*) FROM task_assignments ta JOIN tasks t ON t.id = ta.task_id WHERE ta.user_id = u.id AND t.status NOT IN ('done','cancelled') AND t.deleted_at IS NULL AND t.due_at < NOW()) as overdue_tasks,
                    (SELECT COUNT(*) FROM task_assignments ta JOIN tasks t ON t.id = ta.task_id WHERE ta.user_id = u.id AND t.status = 'done' AND t.completed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as done_30d
             FROM users u WHERE u.status = 'active' ORDER BY u.display_name"
        );
        $categories = $db->fetchAll("SELECT * FROM categories WHERE is_active = 1 AND visibility = 'team' ORDER BY sort_order");
        $matrix = [];
        foreach ($members as $m) { $matrix[$m['id']] = []; }
        if ($members && $categories) {
            $rows = $db->fetchAll("SELECT cu.user_id, cu.category_id, cu.role_in_category, cu.workload FROM category_user cu");
            foreach ($rows as $row) { $matrix[$row['user_id']][$row['category_id']] = $row; }
        }
        View::render('team/matrix', ['pageTitle' => 'Матриця відповідальності', 'members' => $members, 'categories' => $categories, 'matrix' => $matrix]);
    }

    public function assignCategory(): void
    {
        Acl::require('team.manage');
        $db = Db::instance();
        $userId = (int)($_POST['user_id'] ?? 0);
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $role = $_POST['role_in_category'] ?? 'member';
        $responsibilities = $_POST['responsibilities'] ?? '';
        $workload = (int)($_POST['workload'] ?? 2);

        if ($userId <= 0 || $categoryId <= 0) {
            flash('error', 'Невірні дані.');
            redirect(url('/team/matrix'));
        }

        $existing = $db->fetchOne("SELECT id FROM category_user WHERE user_id = ? AND category_id = ?", [$userId, $categoryId]);
        if ($existing) {
            if ($role === 'none') {
                $db->execute("DELETE FROM category_user WHERE id = ?", [$existing['id']]);
                Audit::log('category_unassigned', 'user', $userId, "category:$categoryId");
            } else {
                $db->execute("UPDATE category_user SET role_in_category = ?, responsibilities = ?, workload = ? WHERE id = ?", [$role, $responsibilities, $workload, $existing['id']]);
                Audit::log('category_updated', 'user', $userId, "category:$categoryId");
            }
        } else {
            if ($role !== 'none') {
                $db->execute("INSERT INTO category_user (user_id, category_id, role_in_category, responsibilities, workload, assigned_by) VALUES (?, ?, ?, ?, ?, ?)", [$userId, $categoryId, $role, $responsibilities, $workload, Auth::id()]);
                Audit::log('category_assigned', 'user', $userId, "category:$categoryId");
            }
        }
        flash('success', 'Відповідальність оновлено.');
        redirect(url('/team/matrix'));
    }

    public function show(string $id): void
    {
        Acl::require('team.view');
        $db = Db::instance();
        $member = $db->fetchOne("SELECT u.*, r.title as role_title, r.slug as role_slug FROM users u JOIN roles r ON r.id = u.role_id WHERE u.public_id = ? AND u.status = 'active'", [$id]);
        if (!$member) { http_response_code(404); View::render('errors/404', [], 'layouts/error'); return; }
        $categories = $db->fetchAll("SELECT c.*, cu.role_in_category, cu.responsibilities, cu.workload FROM category_user cu JOIN categories c ON c.id = cu.category_id WHERE cu.user_id = ? AND c.is_active = 1 ORDER BY c.sort_order", [$member['id']]);
        $tasks = $db->fetchAll("SELECT t.public_id, t.code, t.title, t.status, t.priority, t.due_at, t.category_id FROM task_assignments ta JOIN tasks t ON t.id = ta.task_id WHERE ta.user_id = ? AND t.deleted_at IS NULL ORDER BY t.priority DESC, t.due_at ASC LIMIT 50", [$member['id']]);
        View::render('team/show', ['pageTitle' => $member['display_name'], 'member' => $member, 'categories' => $categories, 'tasks' => $tasks]);
    }
}
