<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\{Auth, View, Db, Acl, Validator, Audit, Crypto, Totp};

class AdminController
{
    public function settings(): void
    {
        Acl::require('settings.view');
        $db = Db::instance();
        $settings = $db->fetchAll("SELECT * FROM settings ORDER BY group_name, setting_key");
        $grouped = [];
        foreach ($settings as $s) {
            $grouped[$s['group_name']][] = $s;
        }
        View::render('admin/settings', ['pageTitle' => 'Налаштування', 'grouped' => $grouped]);
    }

    public function updateSettings(): void
    {
        Acl::require('settings.manage');
        if (!Auth::isReAuthenticated()) {
            flash('error', 'Потрібне повторне підтвердження пароля.');
            redirect(url('/admin/settings'));
        }
        $db = Db::instance();
        $keys = $_POST['settings'] ?? [];
        foreach ($keys as $key => $value) {
            $db->execute("UPDATE settings SET setting_value = ?, updated_by = ? WHERE setting_key = ?", [$value, Auth::id(), $key]);
        }
        Audit::log('settings_updated', null, null, null, null, null, null, $keys);
        flash('success', 'Налаштування оновлено.');
        redirect(url('/admin/settings'));
    }

    public function users(): void
    {
        Acl::require('users.create');
        $db = Db::instance();
        $users = $db->fetchAll(
            "SELECT u.*, r.title as role_title, r.slug as role_slug
             FROM users u JOIN roles r ON r.id = u.role_id
             ORDER BY u.created_at DESC"
        );
        $roles = Acl::getRoles();
        View::render('admin/users', ['pageTitle' => 'Користувачі', 'users' => $users, 'roles' => $roles]);
    }

    public function createUser(): void
    {
        Acl::require('users.create');
        $roles = Acl::getRoles();
        View::render('admin/user-form', ['pageTitle' => 'Новий користувач', 'roles' => $roles, 'user' => null]);
    }

    public function storeUser(): void
    {
        Acl::require('users.create');
        $db = Db::instance();

        $v = new Validator($_POST, [
            'login' => 'required|string|min:3|max:32',
            'display_name' => 'required|string|min:2|max:64',
            'password' => 'required|string|min:10',
            'role_id' => 'required|int',
        ]);
        if (!$v->validate()) {
            flash('error', $v->firstError());
            redirect(url('/admin/users/create'));
        }

        $publicId = generate_public_id();
        $hash = password_hash($_POST['password'], PASSWORD_ARGON2ID, ['memory_cost' => 65536, 'time_cost' => 4, 'threads' => 2]);

        try {
            $db->execute(
                "INSERT INTO users (public_id, login, password_hash, display_name, avatar_initials, role_id, status, must_change_password, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, 'active', 1, ?)",
                [$publicId, $_POST['login'], $hash, $_POST['display_name'], mb_substr($_POST['display_name'], 0, 3), (int)$_POST['role_id'], Auth::id()]
            );
            Audit::log('user_created', 'user', (int)$db->lastInsertId(), $_POST['display_name']);
            flash('success', 'Користувача створено.');
        } catch (\Throwable $e) {
            flash('error', 'Помилка: ' . ($e->getMessage() === 'SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry' ? 'Логін вже існує' : $e->getMessage()));
        }
        redirect(url('/admin/users'));
    }

    public function editUser(string $id): void
    {
        Acl::require('users.edit');
        $db = Db::instance();
        $user = $db->fetchOne("SELECT * FROM users WHERE public_id = ?", [$id]);
        if (!$user) { http_response_code(404); View::render('errors/404', [], 'layouts/error'); return; }
        $roles = Acl::getRoles();
        View::render('admin/user-form', ['pageTitle' => 'Редагування', 'user' => $user, 'roles' => $roles]);
    }

    public function updateUser(string $id): void
    {
        Acl::require('users.edit');
        $db = Db::instance();
        $user = $db->fetchOne("SELECT * FROM users WHERE public_id = ?", [$id]);
        if (!$user) { http_response_code(404); return; }

        $updates = [
            'display_name' => $_POST['display_name'] ?? $user['display_name'],
            'role_id' => (int)($_POST['role_id'] ?? $user['role_id']),
            'status' => $_POST['status'] ?? $user['status'],
        ];

        $db->execute(
            "UPDATE users SET display_name = ?, role_id = ?, status = ? WHERE id = ?",
            [$updates['display_name'], $updates['role_id'], $updates['status'], $user['id']]
        );

        if (!empty($_POST['password']) && strlen($_POST['password']) >= 10) {
            $hash = password_hash($_POST['password'], PASSWORD_ARGON2ID, ['memory_cost' => 65536, 'time_cost' => 4, 'threads' => 2]);
            $db->execute("UPDATE users SET password_hash = ?, session_version = session_version + 1 WHERE id = ?", [$hash, $user['id']]);
        }

        Audit::log('user_updated', 'user', $user['id'], $updates['display_name']);
        flash('success', 'Користувача оновлено.');
        redirect(url('/admin/users'));
    }

    public function deleteUser(string $id): void
    {
        Acl::require('users.edit');
        $db = Db::instance();
        $user = $db->fetchOne("SELECT * FROM users WHERE public_id = ?", [$id]);
        if (!$user) { http_response_code(404); return; }
        if ((int)$user['id'] === Auth::id()) {
            flash('error', 'Не можна видалити свій акаунт.');
            redirect(url('/admin/users'));
        }
        $db->execute("UPDATE users SET status = 'deleted' WHERE id = ?", [$user['id']]);
        Audit::log('user_deleted', 'user', $user['id'], $user['display_name']);
        flash('success', 'Користувача видалено.');
        redirect(url('/admin/users'));
    }

    public function audit(): void
    {
        Acl::require('audit.view');
        $logs = Audit::recent(100);
        View::render('admin/audit', ['pageTitle' => 'Журнал дій', 'logs' => $logs]);
    }

    public function logins(): void
    {
        Acl::require('audit.view');
        $db = Db::instance();
        $logs = $db->fetchAll("SELECT ll.*, u.display_name FROM login_logs ll LEFT JOIN users u ON u.id = ll.user_id ORDER BY ll.created_at DESC LIMIT 200");
        View::render('admin/logins', ['pageTitle' => 'Журнал входів', 'logs' => $logs]);
    }
}
