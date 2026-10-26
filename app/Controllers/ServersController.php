<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\{Auth, View, Db, Acl, Validator, Audit, Crypto};

class ServersController
{
    public function index(): void
    {
        Acl::require('servers.view');
        $db = Db::instance();
        $servers = $db->fetchAll(
            "SELECT s.*, ss.is_online, ss.players_online, ss.players_max, ss.map_name, ss.ping_ms, ss.checked_at
             FROM servers s
             LEFT JOIN server_stats ss ON ss.id = (SELECT id FROM server_stats WHERE server_id = s.id ORDER BY checked_at DESC LIMIT 1)
             WHERE s.monitor_enabled = 1
             ORDER BY s.sort_order, s.name"
        );
        View::render('servers/index', ['pageTitle' => 'Сервери', 'servers' => $servers, 'canViewIp' => Acl::can('servers.view_ip'), 'canManage' => Acl::can('servers.manage')]);
    }

    public function show(string $id): void
    {
        Acl::require('servers.view');
        $db = Db::instance();
        $server = $db->fetchOne("SELECT s.*, c.title as category_title FROM servers s LEFT JOIN categories c ON c.id = s.category_id WHERE s.public_id = ?", [$id]);
        if (!$server) { http_response_code(404); View::render('errors/404', [], 'layouts/error'); return; }
        $stats = $db->fetchAll("SELECT * FROM server_stats WHERE server_id = ? AND checked_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) ORDER BY checked_at", [$server['id']]);
        $incidents = $db->fetchAll("SELECT * FROM server_incidents WHERE server_id = ? ORDER BY started_at DESC LIMIT 10", [$server['id']]);
        View::render('servers/show', ['pageTitle' => $server['name'], 'server' => $server, 'stats' => $stats, 'incidents' => $incidents, 'canViewIp' => Acl::can('servers.view_ip'), 'canManage' => Acl::can('servers.manage')]);
    }

    public function create(): void
    {
        Acl::require('servers.manage');
        $db = Db::instance();
        $categories = $db->fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order");
        View::render('servers/form', ['pageTitle' => 'Новий сервер', 'server' => null, 'categories' => $categories]);
    }

    public function store(): void
    {
        Acl::require('servers.manage');
        $db = Db::instance();
        $v = new Validator($_POST, ['name' => 'required|string|min:2|max:80', 'host' => 'required|string', 'port' => 'required|int']);
        if (!$v->validate()) { flash('error', $v->firstError()); redirect(url('/servers/create')); }

        $publicId = generate_public_id();
        $rconEncrypted = null;
        if (!empty($_POST['rcon_password'])) {
            $rconEncrypted = Crypto::encrypt($_POST['rcon_password']);
        }

        $db->execute(
            "INSERT INTO servers (public_id, name, game, protocol, host, port, query_port, rcon_port, rcon_password_encrypted, category_id, is_public_visible, monitor_enabled, last_status, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'unknown', ?)",
            [$publicId, $_POST['name'], $_POST['game'] ?? 'other', $_POST['protocol'] ?? 'source', $_POST['host'], (int)$_POST['port'], $_POST['query_port'] ?: null, $_POST['rcon_port'] ?: null, $rconEncrypted, $_POST['category_id'] ?: null, isset($_POST['is_public_visible']) ? 1 : 0, isset($_POST['monitor_enabled']) ? 1 : 0, (int)($_POST['sort_order'] ?? 0)]
        );
        Audit::log('server_created', 'server', (int)$db->lastInsertId(), $_POST['name']);
        flash('success', 'Сервер додано.');
        redirect(url('/servers'));
    }

    public function edit(string $id): void
    {
        Acl::require('servers.manage');
        $db = Db::instance();
        $server = $db->fetchOne("SELECT * FROM servers WHERE public_id = ?", [$id]);
        if (!$server) { http_response_code(404); View::render('errors/404', [], 'layouts/error'); return; }
        $categories = $db->fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order");
        View::render('servers/form', ['pageTitle' => 'Редагування сервера', 'server' => $server, 'categories' => $categories]);
    }

    public function update(string $id): void
    {
        Acl::require('servers.manage');
        $db = Db::instance();
        $server = $db->fetchOne("SELECT * FROM servers WHERE public_id = ?", [$id]);
        if (!$server) { http_response_code(404); return; }

        $rconEncrypted = $server['rcon_password_encrypted'];
        if (!empty($_POST['rcon_password'])) {
            $rconEncrypted = Crypto::encrypt($_POST['rcon_password']);
        }

        $db->execute(
            "UPDATE servers SET name = ?, game = ?, protocol = ?, host = ?, port = ?, query_port = ?, rcon_port = ?, rcon_password_encrypted = ?, category_id = ?, is_public_visible = ?, monitor_enabled = ?, sort_order = ? WHERE id = ?",
            [$_POST['name'], $_POST['game'] ?? 'other', $_POST['protocol'] ?? 'source', $_POST['host'], (int)$_POST['port'], $_POST['query_port'] ?: null, $_POST['rcon_port'] ?: null, $rconEncrypted, $_POST['category_id'] ?: null, isset($_POST['is_public_visible']) ? 1 : 0, isset($_POST['monitor_enabled']) ? 1 : 0, (int)($_POST['sort_order'] ?? 0), $server['id']]
        );
        Audit::log('server_updated', 'server', $server['id'], $_POST['name']);
        flash('success', 'Сервер оновлено.');
        redirect(url('/servers/' . $id));
    }

    public function delete(string $id): void
    {
        Acl::require('servers.manage');
        $db = Db::instance();
        $server = $db->fetchOne("SELECT * FROM servers WHERE public_id = ?", [$id]);
        if (!$server) { http_response_code(404); return; }
        $db->execute("DELETE FROM servers WHERE id = ?", [$server['id']]);
        Audit::log('server_deleted', 'server', $server['id'], $server['name']);
        flash('success', 'Сервер видалено.');
        redirect(url('/servers'));
    }
}
