<?php
declare(strict_types=1);
/**
 * Дашборд Owner — звідки видно всю команду.
 */
\App\Core\View::startSection('content');
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h1 style="font-size: 1.5rem; font-weight: 700;">Панель керування</h1>
    <a href="<?= url('/tasks/create') ?>" class="btn btn-primary">+ Нове завдання</a>
</div>

<!-- Зведення -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="card" style="text-align: center;">
        <div style="font-size: 2rem; font-weight: 700; color: var(--accent);"><?= (int)($stats['active'] ?? 0) ?></div>
        <div style="font-size: 0.8125rem; color: var(--text-dim);">Активних завдань</div>
    </div>
    <div class="card" style="text-align: center;">
        <div style="font-size: 2rem; font-weight: 700; color: var(--danger);"><?= (int)($stats['overdue'] ?? 0) ?></div>
        <div style="font-size: 0.8125rem; color: var(--text-dim);">Прострочено</div>
    </div>
    <div class="card" style="text-align: center;">
        <div style="font-size: 2rem; font-weight: 700; color: var(--warning);"><?= (int)($stats['review'] ?? 0) ?></div>
        <div style="font-size: 0.8125rem; color: var(--text-dim);">На перевірці</div>
    </div>
    <div class="card" style="text-align: center;">
        <div style="font-size: 2rem; font-weight: 700; color: var(--success);"><?= (int)($stats['done_week'] ?? 0) ?></div>
        <div style="font-size: 0.8125rem; color: var(--text-dim);">Виконано за 7 днів</div>
    </div>
</div>

<!-- Потребує уваги -->
<?php if (!empty($needsAttention)): ?>
<div class="card" style="margin-bottom: 1.5rem;">
    <h2 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">⚡ Потребує моєї уваги</h2>
    <?php foreach ($needsAttention as $item): ?>
        <div style="padding: 0.5rem 0; border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 0.875rem;"><?= e($item['text']) ?></span>
            <span class="badge badge-<?= e($item['severity'] ?? 'info') ?>"><?= e($item['type'] ?? '') ?></span>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Команда — навантаження -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h2 style="font-size: 1rem; font-weight: 600;">👥 Команда</h2>
        <a href="<?= url('/team/matrix') ?>" class="btn btn-sm">Матриця →</a>
    </div>
    <?php if (empty($team)): ?>
        <p style="color: var(--text-muted); font-size: 0.875rem;">Ще немає учасників. <a href="<?= url('/admin/users') ?>">Створити</a></p>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 0.75rem;">
            <?php foreach ($team as $member): ?>
                <a href="<?= url('/team/' . ($member['public_id'] ?? '')) ?>" class="card" style="padding: 1rem; text-decoration: none;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--accent); display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.875rem; color: #fff;">
                            <?= e($member['avatar_initials'] ?? mb_substr($member['display_name'], 0, 1)) ?>
                        </div>
                        <div>
                            <div style="font-size: 0.875rem; font-weight: 500;"><?= e($member['display_name']) ?></div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);"><?= e($member['role_title'] ?? '') ?></div>
                        </div>
                    </div>
                    <div style="margin-top: 0.5rem; font-size: 0.75rem; color: var(--text-dim);">
                        Активних: <?= (int)($member['active_tasks'] ?? 0) ?>
                        <?php if (($member['workload'] ?? 0) >= 4): ?>
                            <span class="badge badge-danger" style="margin-left: 0.5rem;">Перевантажений</span>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Останні події -->
<div class="card">
    <h2 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">📋 Останні події</h2>
    <?php if (empty($recentActivity)): ?>
        <p style="color: var(--text-muted); font-size: 0.875rem;">Поки що нічого немає.</p>
    <?php else: ?>
        <?php foreach (array_slice($recentActivity, 0, 10) as $event): ?>
            <div style="padding: 0.5rem 0; border-bottom: 1px solid var(--line); font-size: 0.875rem;">
                <span style="color: var(--text-dim);"><?= time_ago($event['created_at'] ?? null) ?></span>
                — <?= e($event['display_name'] ?? 'Система') ?>:
                <?= e($event['action'] ?? '') ?>
                <?php if ($event['entity_label'] ?? ''): ?>
                    «<?= e($event['entity_label']) ?>»
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
\App\Core\View::endSection();
