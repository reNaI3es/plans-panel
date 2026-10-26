<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\{Auth, View, Db, Acl};

class ServersController
{
    public function index(): void
    {
        Acl::require('servers.view');
        $db = Db::instance();
        $user = Auth::user();

        $servers = $db->fetchAll(
            "SELECT s.*, ss.is_online, ss.players_online, ss.players_max, ss.map_name, ss.ping_ms, ss.checked_at
             FROM servers s
             LEFT JOIN server_stats ss ON ss.id = (
                 SELECT id FROM server_stats WHERE server_id = s.id ORDER BY checked_at DESC LIMIT 1
             )
             WHERE s.monitor_enabled = 1
             ORDER BY s.sort_order, s.name"
        );

        View::render('servers/index', ['pageTitle' => 'Сервери', 'servers' => $servers, 'canViewIp' => Acl::can('servers.view_ip')]);
    }

    public function show(string $id): void
    {
        Acl::require('servers.view');
        $db = Db::instance();
        $server = $db->fetchOne(
            "SELECT s.*, c.title as category_title
             FROM servers s LEFT JOIN categories c ON c.id = s.category_id
             WHERE s.public_id = ?",
            [$id]
        );
        if (!$server) { http_response_code(404); View::render('errors/404', [], 'layouts/error'); return; }

        // Статистика за последние 24 часа
        $stats = $db->fetchAll(
            "SELECT * FROM server_stats WHERE server_id = ? AND checked_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) ORDER BY checked_at",
            [$server['id']]
        );

        // Последние инциденты
        $incidents = $db->fetchAll(
            "SELECT * FROM server_incidents WHERE server_id = ? ORDER BY started_at DESC LIMIT 10",
            [$server['id']]
        );

        View::render('servers/show', [
            'pageTitle' => $server['name'],
            'server' => $server,
            'stats' => $stats,
            'incidents' => $incidents,
            'canViewIp' => Acl::can('servers.view_ip'),
        ]);
    }
}
