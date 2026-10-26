<?php
declare(strict_types=1);
/**
 * Бічна панель навігації.
 */
$currentUri = $_SERVER['REQUEST_URI'] ?? '';
?>
<aside class="sidebar">
    <div style="margin-bottom: 1.5rem;">
        <a href="<?= url('/dashboard') ?>" style="font-size: 1.25rem; font-weight: 700; color: var(--text);">
            🎮 reNaI3es
        </a>
    </div>

    <nav>
        <a href="<?= url('/dashboard') ?>" class="sidebar-link <?= str_contains($currentUri, '/dashboard') ? 'active' : '' ?>">
            📊 Головна
        </a>
        <a href="<?= url('/tasks') ?>" class="sidebar-link <?= str_contains($currentUri, '/tasks') ? 'active' : '' ?>">
            ✅ Завдання
        </a>
        <a href="<?= url('/categories') ?>" class="sidebar-link <?= str_contains($currentUri, '/categories') ? 'active' : '' ?>">
            📁 Категорії
        </a>
        <a href="<?= url('/team') ?>" class="sidebar-link <?= str_contains($currentUri, '/team') ? 'active' : '' ?>">
            👥 Команда
        </a>
        <a href="<?= url('/kb') ?>" class="sidebar-link <?= str_contains($currentUri, '/kb') ? 'active' : '' ?>">
            📚 База знань
        </a>
        <a href="<?= url('/servers') ?>" class="sidebar-link <?= str_contains($currentUri, '/servers') ? 'active' : '' ?>">
            🖥️ Сервери
        </a>
        <a href="<?= url('/ai') ?>" class="sidebar-link <?= str_contains($currentUri, '/ai') ? 'active' : '' ?>">
            🤖 ШІ-помічник
        </a>
        <a href="<?= url('/notifications') ?>" class="sidebar-link <?= str_contains($currentUri, '/notifications') ? 'active' : '' ?>">
            🔔 Сповіщення
        </a>

        <?php if ($user && $user['role_slug'] === 'owner'): ?>
            <hr style="border-color: var(--line); margin: 1rem 0;">
            <a href="<?= url('/admin/settings') ?>" class="sidebar-link <?= str_contains($currentUri, '/admin') ? 'active' : '' ?>">
                ⚙️ Налаштування
            </a>
        <?php endif; ?>
    </nav>

    <div style="margin-top: auto; padding-top: 1rem; border-top: 1px solid var(--line);">
        <div style="font-size: 0.8125rem; color: var(--text-dim);">
            <?= e($user['display_name'] ?? '') ?>
            <br><span style="font-size: 0.75rem; color: var(--text-muted);"><?= e($user['role_title'] ?? '') ?></span>
        </div>
        <a href="<?= url('/logout') ?>" style="font-size: 0.8125rem; color: var(--danger); margin-top: 0.5rem; display: block;">
            Вийти
        </a>
    </div>
</aside>

<style>
.sidebar-link { display: flex; align-items: center; gap: 0.5rem; padding: 0.625rem 0.75rem; border-radius: var(--radius); color: var(--text-dim); font-size: 0.875rem; transition: all var(--transition); margin-bottom: 0.25rem; }
.sidebar-link:hover { background: var(--bg-hover); color: var(--text); }
.sidebar-link.active { background: var(--bg-hover); color: var(--accent); font-weight: 500; }
</style>
