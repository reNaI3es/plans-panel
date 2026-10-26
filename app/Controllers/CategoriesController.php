<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\{Auth, View, Db, Acl};

class CategoriesController
{
    public function index(): void
    {
        $db = Db::instance();
        $user = Auth::user();
        $vis = $user['role_slug'] === 'owner' ? ['team','owner_only'] : ['team'];
        $placeholders = implode(',', array_fill(0, count($vis), '?'));

        $categories = $db->fetchAll(
            "SELECT c.*,
                    (SELECT COUNT(DISTINCT cu.user_id) FROM category_user cu WHERE cu.category_id = c.id) as members_count,
                    (SELECT COUNT(*) FROM tasks t WHERE t.category_id = c.id AND t.status NOT IN ('done','cancelled') AND t.deleted_at IS NULL) as active_tasks
             FROM categories c
             WHERE c.is_active = 1 AND c.visibility IN ($placeholders)
             ORDER BY c.sort_order",
            $vis
        );

        View::render('categories/index', ['pageTitle' => 'Категорії', 'categories' => $categories]);
    }

    public function show(string $id): void
    {
        $db = Db::instance();
        $cat = $db->fetchOne("SELECT * FROM categories WHERE public_id = ? AND is_active = 1", [$id]);
        if (!$cat) { http_response_code(404); View::render('errors/404', [], 'layouts/error'); return; }

        $members = $db->fetchAll(
            "SELECT u.id, u.public_id, u.display_name, u.avatar_initials, cu.role_in_category, cu.responsibilities, cu.workload
             FROM category_user cu JOIN users u ON u.id = cu.user_id
             WHERE cu.category_id = ? AND u.status = 'active'
             ORDER BY cu.role_in_category = 'lead' DESC, u.display_name",
            [$cat['id']]
        );

        $tasks = $db->fetchAll(
            "SELECT t.public_id, t.code, t.title, t.status, t.priority, t.due_at
             FROM tasks t WHERE t.category_id = ? AND t.deleted_at IS NULL
             ORDER BY t.priority DESC, t.due_at ASC LIMIT 50",
            [$cat['id']]
        );

        View::render('categories/show', ['pageTitle' => $cat['title'], 'category' => $cat, 'members' => $members, 'tasks' => $tasks]);
    }
}
