<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\{Auth, View, Db, Acl, Validator, Audit};

/**
 * Контролер завдань — створення, перегляд, редагування, kanban, календар.
 */
class TasksController
{
    public function index(): void
    {
        $user = Auth::user();
        $db = Db::instance();

        // Фільтри
        $status = $_GET['status'] ?? '';
        $priority = $_GET['priority'] ?? '';
        $type = $_GET['type'] ?? '';
        $search = $_GET['q'] ?? '';

        $where = ["t.deleted_at IS NULL"];
        $params = [];

        // Для member — тільки свої завдання
        if ($user['role_slug'] === 'member' || $user['role_slug'] === 'viewer') {
            $where[] = "ta.user_id = ?";
            $params[] = $user['id'];
        }

        if ($status) {
            $where[] = "t.status = ?";
            $params[] = $status;
        }
        if ($priority) {
            $where[] = "t.priority = ?";
            $params[] = $priority;
        }
        if ($type) {
            $where[] = "t.type = ?";
            $params[] = $type;
        }
        if ($search) {
            $where[] = "(t.title LIKE ? OR t.code LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $whereStr = implode(' AND ', $where);

        $tasks = $db->fetchAll(
            "SELECT t.*, ta.status as assign_status, ta.progress as assign_progress,
                    u.display_name as creator_name
             FROM tasks t
             LEFT JOIN task_assignments ta ON ta.task_id = t.id AND ta.user_id = ?
             LEFT JOIN users u ON u.id = t.created_by
             WHERE $whereStr
             ORDER BY t.priority DESC, t.due_at ASC
             LIMIT 100",
            array_merge([$user['id']], $params)
        );

        View::render('tasks/index', [
            'pageTitle' => 'Завдання',
            'tasks' => $tasks,
            'filters' => compact('status', 'priority', 'type', 'search'),
        ]);
    }

    public function show(string $id): void
    {
        $db = Db::instance();
        $task = $db->fetchOne(
            "SELECT t.*, u.display_name as creator_name, c.title as category_title
             FROM tasks t
             LEFT JOIN users u ON u.id = t.created_by
             LEFT JOIN categories c ON c.id = t.category_id
             WHERE t.public_id = ? AND t.deleted_at IS NULL",
            [$id]
        );

        if (!$task) {
            http_response_code(404);
            View::render('errors/404', [], 'layouts/error');
            return;
        }

        // Перевірка доступу
        $user = Auth::user();
        if ($user['role_slug'] === 'member') {
            $assignment = $db->fetchOne(
                "SELECT id FROM task_assignments WHERE task_id = ? AND user_id = ?",
                [$task['id'], $user['id']]
            );
            if (!$assignment && (int)$task['created_by'] !== (int)$user['id']) {
                http_response_code(403);
                View::render('errors/403', [], 'layouts/error');
                return;
            }
        }

        // Коментарі
        $comments = $db->fetchAll(
            "SELECT tc.*, u.display_name, u.avatar_initials
             FROM task_comments tc
             JOIN users u ON u.id = tc.user_id
             WHERE tc.task_id = ? AND tc.deleted_at IS NULL
             ORDER BY tc.created_at ASC",
            [$task['id']]
        );

        // Чеклист
        $checklist = $db->fetchAll(
            "SELECT * FROM task_checklist WHERE task_id = ? ORDER BY position",
            [$task['id']]
        );

        // Історія статусів
        $history = $db->fetchAll(
            "SELECT tsh.*, u.display_name
             FROM task_status_history tsh
             JOIN users u ON u.id = tsh.user_id
             WHERE tsh.task_id = ?
             ORDER BY tsh.created_at DESC
             LIMIT 20",
            [$task['id']]
        );

        View::render('tasks/show', [
            'pageTitle' => ($task['code'] ?? '') . ' — ' . ($task['title'] ?? ''),
            'task' => $task,
            'comments' => $comments,
            'checklist' => $checklist,
            'history' => $history,
        ]);
    }

    public function create(): void
    {
        Acl::require('tasks.create');
        $db = Db::instance();

        $categories = $db->fetchAll(
            "SELECT * FROM categories WHERE is_active = 1 AND (visibility = 'team' OR ? = 'owner') ORDER BY sort_order",
            [Auth::user()['role_slug'] ?? '']
        );
        $users = $db->fetchAll("SELECT id, display_name, public_id FROM users WHERE status = 'active' ORDER BY display_name");

        View::render('tasks/create', [
            'pageTitle' => 'Нове завдання',
            'categories' => $categories,
            'users' => $users,
        ]);
    }

    public function store(): void
    {
        Acl::require('tasks.create');
        $db = Db::instance();

        $v = new Validator($_POST, [
            'title' => 'required|string|min:3|max:200',
        ]);
        if (!$v->validate()) {
            flash('error', $v->firstError());
            redirect(url('/tasks/create'));
        }

        // Генеруємо код завдання
        $lastCode = (int)$db->fetchColumn("SELECT MAX(CAST(SUBSTRING(code, 4) AS UNSIGNED)) FROM tasks");
        $code = 'RN-' . str_pad((string)($lastCode + 1), 6, '0', STR_PAD_LEFT);
        $publicId = generate_public_id();

        $db->execute(
            "INSERT INTO tasks (public_id, code, title, description, type, priority, status, assign_mode, category_id, created_by, due_at, require_review)
             VALUES (?, ?, ?, ?, ?, ?, 'new', ?, ?, ?, ?, ?)",
            [
                $publicId,
                $code,
                $_POST['title'],
                $_POST['description'] ?? null,
                $_POST['type'] ?? 'additional',
                $_POST['priority'] ?? 'normal',
                $_POST['assign_mode'] ?? 'single',
                $_POST['category_id'] ?: null,
                Auth::id(),
                $_POST['due_at'] ?: null,
                isset($_POST['require_review']) ? 1 : 0,
            ]
        );
        $taskId = (int)$db->lastInsertId();

        // Призначення
        $assignUsers = $_POST['assign_users'] ?? [];
        if (!is_array($assignUsers)) $assignUsers = [$assignUsers];

        foreach ($assignUsers as $uid) {
            $uid = (int)$uid;
            if ($uid <= 0) continue;
            $db->execute(
                "INSERT INTO task_assignments (task_id, user_id, due_at) VALUES (?, ?, ?)",
                [$taskId, $uid, $_POST['due_at'] ?: null]
            );
        }

        // Історія
        $db->execute(
            "INSERT INTO task_status_history (task_id, user_id, from_status, to_status) VALUES (?, ?, NULL, 'new')",
            [$taskId, Auth::id()]
        );

        Audit::log('task_created', 'task', $taskId, $_POST['title']);
        flash('success', "Завдання {$code} створено.");
        redirect(url('/tasks/' . $publicId));
    }

    public function changeStatus(string $id): void
    {
        $db = Db::instance();
        $task = $db->fetchOne("SELECT * FROM tasks WHERE public_id = ? AND deleted_at IS NULL", [$id]);
        if (!$task) {
            http_response_code(404);
            return;
        }

        $newStatus = $_POST['status'] ?? '';
        $allowed = ['new', 'in_progress', 'on_hold', 'review', 'done', 'cancelled'];
        if (!in_array($newStatus, $allowed, true)) {
            flash('error', 'Недопустимий статус.');
            redirect(url('/tasks/' . $id));
        }

        $user = Auth::user();
        $oldStatus = $task['status'];

        // Member не може сам поставити done, якщо require_review
        if ($user['role_slug'] === 'member' && $newStatus === 'done' && $task['require_review']) {
            $newStatus = 'review';
        }

        // Оновлюємо assignment
        $db->execute(
            "UPDATE task_assignments SET status = ?, done_at = IF(? = 'done', NOW(), NULL) WHERE task_id = ? AND user_id = ?",
            [$newStatus, $newStatus, $task['id'], $user['id']]
        );

        // Оновлюємо статус завдання
        $completedAt = $newStatus === 'done' ? 'NOW()' : 'NULL';
        $db->execute(
            "UPDATE tasks SET status = ?, completed_at = {$completedAt} WHERE id = ?",
            [$newStatus, $task['id']]
        );

        // Історія
        $db->execute(
            "INSERT INTO task_status_history (task_id, user_id, from_status, to_status) VALUES (?, ?, ?, ?)",
            [$task['id'], $user['id'], $oldStatus, $newStatus]
        );

        Audit::log('task_status_changed', 'task', $task['id'], $task['title'], null, null, ['status' => $oldStatus], ['status' => $newStatus]);
        flash('success', 'Статус оновлено.');
        redirect(url('/tasks/' . $id));
    }

    public function addComment(string $id): void
    {
        $db = Db::instance();
        $task = $db->fetchOne("SELECT * FROM tasks WHERE public_id = ? AND deleted_at IS NULL", [$id]);
        if (!$task) {
            http_response_code(404);
            return;
        }

        $body = trim($_POST['body'] ?? '');
        if (strlen($body) < 1) {
            flash('error', 'Коментар не може бути порожнім.');
            redirect(url('/tasks/' . $id));
        }

        $db->execute(
            "INSERT INTO task_comments (task_id, user_id, body) VALUES (?, ?, ?)",
            [$task['id'], Auth::id(), $body]
        );

        Audit::log('comment_added', 'task', $task['id'], $task['title']);
        redirect(url('/tasks/' . $id));
    }

    public function kanban(): void
    {
        $db = Db::instance();
        $user = Auth::user();

        $statuses = ['new', 'in_progress', 'on_hold', 'review', 'done'];
        $columns = [];

        foreach ($statuses as $status) {
            if ($user['role_slug'] === 'member') {
                $tasks = $db->fetchAll(
                    "SELECT t.public_id, t.code, t.title, t.priority, t.due_at
                     FROM task_assignments ta
                     JOIN tasks t ON t.id = ta.task_id
                     WHERE ta.user_id = ? AND t.status = ? AND t.deleted_at IS NULL
                     ORDER BY t.priority DESC, t.due_at ASC",
                    [$user['id'], $status]
                );
            } else {
                $tasks = $db->fetchAll(
                    "SELECT public_id, code, title, priority, due_at
                     FROM tasks WHERE status = ? AND deleted_at IS NULL
                     ORDER BY priority DESC, due_at ASC",
                    [$status]
                );
            }
            $columns[$status] = $tasks;
        }

        View::render('tasks/kanban', [
            'pageTitle' => 'Канбан',
            'columns' => $columns,
        ]);
    }

    public function calendar(): void
    {
        $year = (int)($_GET['year'] ?? date('Y'));
        $month = (int)($_GET['month'] ?? date('m'));
        if ($month < 1) { $month = 12; $year--; }
        if ($month > 12) { $month = 1; $year++; }

        $db = Db::instance();
        $user = Auth::user();

        $firstDay = "{$year}-{$month}-01";
        $lastDay = date('Y-m-t', strtotime($firstDay));

        if ($user['role_slug'] === 'member') {
            $tasks = $db->fetchAll(
                "SELECT t.public_id, t.code, t.title, t.due_at, t.priority, t.status
                 FROM task_assignments ta
                 JOIN tasks t ON t.id = ta.task_id
                 WHERE ta.user_id = ? AND t.due_at BETWEEN ? AND ? AND t.deleted_at IS NULL
                 ORDER BY t.due_at",
                [$user['id'], $firstDay, $lastDay . ' 23:59:59']
            );
        } else {
            $tasks = $db->fetchAll(
                "SELECT public_id, code, title, due_at, priority, status
                 FROM tasks WHERE due_at BETWEEN ? AND ? AND deleted_at IS NULL
                 ORDER BY due_at",
                [$firstDay, $lastDay . ' 23:59:59']
            );
        }

        // Групуємо по дням
        $byDay = [];
        foreach ($tasks as $t) {
            $day = date('j', strtotime($t['due_at']));
            $byDay[$day][] = $t;
        }

        View::render('tasks/calendar', [
            'pageTitle' => 'Календар',
            'year' => $year,
            'month' => $month,
            'byDay' => $byDay,
        ]);
    }
}
