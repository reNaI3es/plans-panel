<?php
declare(strict_types=1);
\App\Core\View::startSection('content');
?>

<h1 class="page-title">Вітаємо, <span class="accent"><?= e($user['display_name'] ?? '') ?></span></h1>

<!-- Метрики -->
<div class="metrics">
    <div class="metric">
        <div class="metric-value"><?= (int)($stats['active'] ?? 0) ?></div>
        <div class="metric-label">Активних завдань</div>
    </div>
    <div class="metric">
        <div class="metric-value danger"><?= (int)($stats['overdue'] ?? 0) ?></div>
        <div class="metric-label">Прострочено</div>
    </div>
    <div class="metric">
        <div class="metric-value warn"><?= (int)($stats['review'] ?? 0) ?></div>
        <div class="metric-label">На перевірці</div>
    </div>
    <div class="metric">
        <div class="metric-value ok"><?= (int)($stats['done_week'] ?? 0) ?></div>
        <div class="metric-label">Виконано за 7 днів</div>
    </div>
</div>

<!-- Контент: завдання + сервери -->
<div class="content-grid">
    <div>
        <!-- Потребує уваги -->
        <?php if (!empty($needsAttention)): ?>
        <div style="margin-bottom: 48px;">
            <h2>Потребує уваги</h2>
            <ul class="row-list">
                <?php foreach ($needsAttention as $item): ?>
                <li class="row-item">
                    <div class="dot dot-<?= $item['severity'] === 'danger' ? 'danger' : ($item['severity'] === 'warning' ? 'warn' : 'muted') ?>"></div>
                    <div class="row-item-main">
                        <div class="row-item-title"><?= e($item['text']) ?></div>
                    </div>
                    <div class="row-item-right">
                        <span class="badge badge-<?= $item['severity'] === 'danger' ? 'danger' : 'warn' ?>"><?= e($item['type'] ?? '') ?></span>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Останні завдання -->
        <div>
            <div class="panel-header">
                <h2 style="margin-bottom:0">Останні завдання</h2>
                <a href="<?= url('/tasks') ?>" class="btn btn-sm">
                    Усі
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            </div>
            <?php if (empty($recentTasks)): ?>
                <div class="empty">Завдань поки немає. <a href="<?= url('/tasks/create') ?>">Створити перше</a></div>
            <?php else: ?>
                <ul class="row-list">
                    <?php foreach (array_slice($recentTasks ?? $recentActivity ?? [], 0, 8) as $task): ?>
                    <li class="row-item">
                        <div class="row-item-main">
                            <div class="row-item-title">
                                <a href="<?= url('/tasks/' . ($task['public_id'] ?? '')) ?>"><?= e($task['title'] ?? $task['entity_label'] ?? '') ?></a>
                            </div>
                            <div class="row-item-meta">
                                <?php if ($task['code'] ?? ''): ?><span style="font-family:monospace;font-size:11px"><?= e($task['code']) ?></span> &middot; <?php endif; ?>
                                <?= time_ago($task['created_at'] ?? $task['updated_at'] ?? null) ?>
                            </div>
                        </div>
                        <div class="row-item-right">
                            <?php if ($task['status'] ?? ''): ?>
                                <span class="badge badge-<?= $task['status'] === 'done' ? 'ok' : ($task['status'] === 'review' ? 'warn' : 'accent') ?>"><?= e($task['status']) ?></span>
                            <?php endif; ?>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <!-- Права колонка: сервери + команда -->
    <div>
        <div style="margin-bottom: 32px;">
            <div class="panel-header">
                <h2 style="margin-bottom:0">Сервери</h2>
                <a href="<?= url('/servers') ?>" class="btn btn-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            </div>
            <?php if (empty($servers ?? [])): ?>
                <div class="empty" style="padding:24px 0">Сервери не додано</div>
            <?php else: ?>
                <?php foreach (array_slice($servers ?? [], 0, 5) as $srv): ?>
                <div class="server-row">
                    <div class="dot dot-<?= ($srv['last_status'] ?? 'unknown') === 'online' ? 'ok' : 'danger' ?>"></div>
                    <div class="server-name"><?= e($srv['name'] ?? '') ?></div>
                    <div class="server-stats">
                        <?php if ($srv['players_online'] ?? null): ?>
                            <span><?= (int)$srv['players_online'] ?>/<?= (int)$srv['players_max'] ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div>
            <div class="panel-header">
                <h2 style="margin-bottom:0">Команда</h2>
                <a href="<?= url('/team/matrix') ?>" class="btn btn-sm">Матриця</a>
            </div>
            <?php if (empty($team ?? [])): ?>
                <div class="empty" style="padding:24px 0">Учасників немає. <a href="<?= url('/admin/users') ?>">Додати</a></div>
            <?php else: ?>
                <ul class="row-list">
                    <?php foreach (array_slice($team ?? [], 0, 6) as $member): ?>
                    <li class="row-item">
                        <div style="width:28px;height:28px;border-radius:50%;background:var(--panel-2);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;color:var(--accent);flex-shrink:0">
                            <?= e(mb_substr($member['display_name'] ?? '?', 0, 1)) ?>
                        </div>
                        <div class="row-item-main">
                            <div class="row-item-title" style="font-size:13px"><?= e($member['display_name'] ?? '') ?></div>
                            <div class="row-item-meta"><?= e($member['role_title'] ?? '') ?></div>
                        </div>
                        <div class="row-item-right">
                            <span style="font-size:11px;color:var(--mute)"><?= (int)($member['active_tasks'] ?? 0) </span>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
\App\Core\View::endSection();
