<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\{Auth, View, Db};

class NotificationsController
{
    public function index(): void
    {
        $db = Db::instance();
        $notifications = $db->fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 100",
            [Auth::id()]
        );
        $unreadCount = count(array_filter($notifications, fn($n) => !$n['is_read']));
        View::render('notifications/index', ['pageTitle' => 'Сповіщення', 'notifications' => $notifications, 'unreadCount' => $unreadCount]);
    }

    public function markRead(): void
    {
        $db = Db::instance();
        $db->execute("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0", [Auth::id()]);
        flash('success', 'Усі сповіщення прочитано.');
        redirect(url('/notifications'));
    }
}
