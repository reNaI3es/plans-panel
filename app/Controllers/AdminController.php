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
            $db->execute(
                "UPDATE settings SET setting_value = ?, updated_by = ? WHERE setting_key = ?",
                [$value, Auth::id(), $key]
            );
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
        $logs = $db->fetchAll(
            "SELECT ll.*, u.display_name
             FROM login_logs ll LEFT JOIN users u ON u.id = ll.user_id
             ORDER BY ll.created_at DESC LIMIT 200"
        );
        View::render('admin/logins', ['pageTitle' => 'Журнал входів', 'logs' => $logs]);
    }
}
