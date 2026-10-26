<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\{Auth, View, Db, Acl};

class KbController
{
    public function index(): void
    {
        Acl::require('kb.view');
        $db = Db::instance();
        $sections = $db->fetchAll("SELECT * FROM kb_sections WHERE is_active = 1 ORDER BY sort_order");
        $pinned = $db->fetchAll(
            "SELECT a.*, s.title as section_title
             FROM kb_articles a LEFT JOIN kb_sections s ON s.id = a.section_id
             WHERE a.is_pinned = 1 AND (a.visibility = 'all' OR a.author_id = ?)
             ORDER BY a.updated_at DESC LIMIT 10",
            [Auth::id()]
        );
        $recent = $db->fetchAll(
            "SELECT a.*, s.title as section_title
             FROM kb_articles a LEFT JOIN kb_sections s ON s.id = a.section_id
             WHERE a.visibility = 'all'
             ORDER BY a.updated_at DESC LIMIT 10"
        );
        View::render('kb/index', ['pageTitle' => 'База знань', 'sections' => $sections, 'pinned' => $pinned, 'recent' => $recent]);
    }

    public function article(string $id): void
    {
        Acl::require('kb.view');
        $db = Db::instance();
        $article = $db->fetchOne(
            "SELECT a.*, s.title as section_title, u.display_name as author_name
             FROM kb_articles a
             LEFT JOIN kb_sections s ON s.id = a.section_id
             LEFT JOIN users u ON u.id = a.author_id
             WHERE a.public_id = ?",
            [$id]
        );
        if (!$article) { http_response_code(404); View::render('errors/404', [], 'layouts/error'); return; }

        // Проверка видимости
        if ($article['visibility'] === 'owner_only' && Auth::user()['role_slug'] !== 'owner') {
            http_response_code(403); View::render('errors/403', [], 'layouts/error'); return;
        }

        // Увеличиваем счётчик просмотров
        $db->execute("UPDATE kb_articles SET views = views + 1 WHERE id = ?", [$article['id']]);

        View::render('kb/article', ['pageTitle' => $article['title'], 'article' => $article]);
    }

    public function search(): void
    {
        Acl::require('kb.view');
        $q = trim($_GET['q'] ?? '');
        $db = Db::instance();
        $results = [];
        if (strlen($q) >= 2) {
            $results = $db->fetchAll(
                "SELECT a.*, s.title as section_title
                 FROM kb_articles a LEFT JOIN kb_sections s ON s.id = a.section_id
                 WHERE a.visibility = 'all' AND (a.title LIKE ? OR a.body LIKE ? OR MATCH(a.title, a.body) AGAINST(? IN BOOLEAN MODE))
                 ORDER BY a.updated_at DESC LIMIT 50",
                ["%$q%", "%$q%", $q]
            );
        }
        View::render('kb/search', ['pageTitle' => 'Пошук', 'query' => $q, 'results' => $results]);
    }

    public function links(): void
    {
        Acl::require('kb.view');
        $db = Db::instance();
        $user = Auth::user();
        $canViewSecrets = Acl::can('kb.view_secrets');

        $links = $db->fetchAll(
            "SELECT * FROM kb_links WHERE is_active = 1 AND (is_secret = 0 OR ? = 1) ORDER BY group_name, sort_order",
            [(int)$canViewSecrets]
        );

        // Группируем
        $grouped = [];
        foreach ($links as $link) {
            $grouped[$link['group_name']][] = $link;
        }

        View::render('kb/links', ['pageTitle' => 'Посилання', 'grouped' => $grouped, 'canViewSecrets' => $canViewSecrets]);
    }
}
