<?php
declare(strict_types=1);
/**
 * Бічна панель навігації.
 */
$currentUri = $_SERVER['REQUEST_URI'] ?? '';
$isActive = fn(string $path) => str_contains($currentUri, $path) ? 'active' : '';
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <a href="<?= url('/dashboard') ?>">reNaI3es</a>
    </div>

    <nav class="sidebar-nav">
        <div class="sidebar-section">
            <a href="<?= url('/dashboard') ?>" class="sidebar-link <?= $isActive('/dashboard') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                Головна
            </a>
            <a href="<?= url('/tasks') ?>" class="sidebar-link <?= $isActive('/tasks') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                Завдання
            </a>
            <a href="<?= url('/categories') ?>" class="sidebar-link <?= $isActive('/categories') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
                Категорії
            </a>
            <a href="<?= url('/team') ?>" class="sidebar-link <?= $isActive('/team') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                Команда
            </a>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-section-label">Ресурси</div>
            <a href="<?= url('/kb') ?>" class="sidebar-link <?= $isActive('/kb') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
                База знань
            </a>
            <a href="<?= url('/servers') ?>" class="sidebar-link <?= $isActive('/servers') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/></svg>
                Сервери
            </a>
            <a href="<?= url('/ai') ?>" class="sidebar-link <?= $isActive('/ai') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 8V4H8"/><rect x="4" y="8" width="16" height="12" rx="2"/><path d="M2 14h2"/><path d="M20 14h2"/><path d="M15 13v2"/><path d="M9 13v2"/></svg>
                ШІ-помічник
            </a>
            <a href="<?= url('/notifications') ?>" class="sidebar-link <?= $isActive('/notifications') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                Сповіщення
            </a>
        </div>

        <?php if ($user && $user['role_slug'] === 'owner'): ?>
        <div class="sidebar-section">
            <div class="sidebar-section-label">Адмін</div>
            <a href="<?= url('/admin/settings') ?>" class="sidebar-link <?= $isActive('/admin') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06A1.65 1.65 0 009 4.68 1.65 1.65 0 0010 3.17V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06A1.65 1.65 0 0019.32 9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                Налаштування
            </a>
        </div>
        <?php endif; ?>
    </nav>

    <div class="sidebar-user">
        <div class="sidebar-user-name"><?= e($user['display_name'] ?? '') ?></div>
        <div class="sidebar-user-role"><?= e($user['role_title'] ?? '') ?></div>
        <a href="<?= url('/logout') ?>" class="sidebar-logout">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Вийти
        </a>
    </div>
</aside>
