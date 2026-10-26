<?php
declare(strict_types=1);
\App\Core\View::startSection('content');
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h1 style="font-size: 1.5rem; font-weight: 700;">📋 Канбан</h1>
    <div style="display: flex; gap: 0.5rem;">
        <a href="<?= url('/tasks') ?>" class="btn btn-sm">📝 Список</a>
        <a href="<?= url('/tasks/calendar') ?>" class="btn btn-sm">📅 Календар</a>
    </div>
</div>

<div class="kanban-board">
    <?php
    $statusLabels = [
        'new' => '🆕 Нові',
        'in_progress' => '🔄 В роботі',
        'on_hold' => '⏸️ На паузі',
        'review' => '👀 На перевірці',
        'done' => '✅ Виконано',
    ];
    foreach ($columns as $status => $tasks):
    ?>
        <div class="kanban-column" data-status="<?= e($status) ?>">
            <div class="kanban-column-title"><?= e($statusLabels[$status] ?? $status) ?> (<?= count($tasks) ?>)</div>
            <div class="kanban-list">
                <?php foreach ($tasks as $task): ?>
                    <div class="kanban-card" data-id="<?= e($task['public_id'] ?? '') ?>" data-status-url="<?= url('/tasks/' . ($task['public_id'] ?? '') . '/status') ?>">
                        <div style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">
                            <?= e($task['code'] ?? '') ?>
                        </div>
                        <div style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">
                            <a href="<?= url('/tasks/' . ($task['public_id'] ?? '')) ?>"><?= e($task['title'] ?? '') ?></a>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem;">
                            <span class="badge badge-<?= $task['priority'] === 'critical' ? 'danger' : ($task['priority'] === 'high' ? 'warning' : 'info') ?>">
                                <?= e($task['priority'] ?? '') ?>
                            </span>
                            <?php if ($task['due_at'] ?? ''): ?>
                                <span style="color: var(--text-muted);"><?= dt($task['due_at'], 'd.m') ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($tasks)): ?>
                    <div style="text-align: center; padding: 2rem; color: var(--text-muted); font-size: 0.8125rem;">
                        Порожньо
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php
\App\Core\View::endSection();
