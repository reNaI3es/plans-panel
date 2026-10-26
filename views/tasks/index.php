<?php
declare(strict_types=1);
\App\Core\View::startSection('content');
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:32px">
    <h1 class="page-title" style="margin-bottom:0">Завдання</h1>
    <div style="display:flex;gap:8px">
        <a href="<?= url('/tasks/kanban') ?>" class="btn btn-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            Канбан
        </a>
        <a href="<?= url('/tasks/calendar') ?>" class="btn btn-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Календар
        </a>
        <?php if (\App\Core\Acl::can('tasks.create')): ?>
        <a href="<?= url('/tasks/create') ?>" class="btn btn-primary btn-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Нове
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Фільтри -->
<div style="margin-bottom:24px;display:flex;gap:8px;flex-wrap:wrap;align-items:end">
    <div style="flex:1;min-width:180px">
        <form method="GET" action="<?= url('/tasks') ?>" style="display:flex;gap:8px;flex-wrap:wrap;align-items:end">
            <div style="flex:1;min-width:160px">
                <input type="text" name="q" value="<?= e($filters['search'] ?? '') ?>" placeholder="Пошук...">
            </div>
            <select name="status">
                <option value="">Усі статуси</option>
                <option value="new" <?= ($filters['status'] ?? '') === 'new' ? 'selected' : '' ?>>Нове</option>
                <option value="in_progress" <?= ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>В роботі</option>
                <option value="on_hold" <?= ($filters['status'] ?? '') === 'on_hold' ? 'selected' : '' ?>>На паузі</option>
                <option value="review" <?= ($filters['status'] ?? '') === 'review' ? 'selected' : '' ?>>На перевірці</option>
                <option value="done" <?= ($filters['status'] ?? '') === 'done' ? 'selected' : '' ?>>Виконано</option>
            </select>
            <select name="priority">
                <option value="">Усі пріоритети</option>
                <option value="low" <?= ($filters['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Низький</option>
                <option value="normal" <?= ($filters['priority'] ?? '') === 'normal' ? 'selected' : '' ?>>Звичайний</option>
                <option value="high" <?= ($filters['priority'] ?? '') === 'high' ? 'selected' : '' ?>>Високий</option>
                <option value="critical" <?= ($filters['priority'] ?? '') === 'critical' ? 'selected' : '' ?>>Критичний</option>
            </select>
            <button type="submit" class="btn btn-sm">Фільтрувати</button>
        </form>
    </div>
</div>

<!-- Таблиця завдань -->
<?php if (empty($tasks)): ?>
    <div class="empty">Завдань не знайдено. <?php if (\App\Core\Acl::can('tasks.create')): ?><a href="<?= url('/tasks/create') ?>">Створити перше</a><?php endif; ?></div>
<?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Код</th>
                    <th>Назва</th>
                    <th>Пріоритет</th>
                    <th>Статус</th>
                    <th>Строк</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                    <?php $isOverdue = $task['due_at'] && strtotime($task['due_at']) < time() && !in_array($task['status'], ['done', 'cancelled']); ?>
                    <tr>
                        <td><span style="font-family:monospace;font-size:12px;color:var(--mute)"><?= e($task['code'] ?? '') ?></span></td>
                        <td>
                            <a href="<?= url('/tasks/' . ($task['public_id'] ?? '')) ?>" style="font-weight:500;color:var(--text)">
                                <?= e($task['title'] ?? '') ?>
                            </a>
                        </td>
                        <td>
                            <span class="badge badge-<?= $task['priority'] === 'critical' ? 'danger' : ($task['priority'] === 'high' ? 'warn' : 'accent') ?>">
                                <?= e($task['priority'] ?? '') ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?= $isOverdue ? 'danger' : ($task['status'] === 'done' ? 'ok' : ($task['status'] === 'review' ? 'warn' : 'accent')) ?>">
                                <?= e(match($task['status']) {
                                    'new' => 'Нове',
                                    'in_progress' => 'В роботі',
                                    'on_hold' => 'На паузі',
                                    'review' => 'На перевірці',
                                    'done' => 'Виконано',
                                    'cancelled' => 'Скасовано',
                                    default => $task['status'] ?? '',
                                }) ?>
                            </span>
                        </td>
                        <td style="font-size:12px;color:<?= $isOverdue ? 'var(--danger)' : 'var(--mute)' ?>">
                            <?= $task['due_at'] ? dt($task['due_at'], 'd.m.Y') : '—' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php
\App\Core\View::endSection();
