<?php
declare(strict_types=1);
\App\Core\View::startSection('content');
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h1 style="font-size: 1.5rem; font-weight: 700;">✅ Завдання</h1>
    <div style="display: flex; gap: 0.5rem;">
        <a href="<?= url('/tasks/kanban') ?>" class="btn btn-sm">📋 Канбан</a>
        <a href="<?= url('/tasks/calendar') ?>" class="btn btn-sm">📅 Календар</a>
        <?php if (\App\Core\Acl::can('tasks.create')): ?>
            <a href="<?= url('/tasks/create') ?>" class="btn btn-primary btn-sm">+ Нове</a>
        <?php endif; ?>
    </div>
</div>

<!-- Фільтри -->
<div class="card" style="margin-bottom: 1.5rem;">
    <form method="GET" action="<?= url('/tasks') ?>" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: end;">
        <div style="flex: 1; min-width: 200px;">
            <label for="q">Пошук</label>
            <input type="text" id="q" name="q" value="<?= e($filters['search'] ?? '') ?>" placeholder="Код, назва...">
        </div>
        <div>
            <label for="status">Статус</label>
            <select id="status" name="status">
                <option value="">Усі</option>
                <option value="new" <?= ($filters['status'] ?? '') === 'new' ? 'selected' : '' ?>>Нове</option>
                <option value="in_progress" <?= ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>В роботі</option>
                <option value="on_hold" <?= ($filters['status'] ?? '') === 'on_hold' ? 'selected' : '' ?>>На паузі</option>
                <option value="review" <?= ($filters['status'] ?? '') === 'review' ? 'selected' : '' ?>>На перевірці</option>
                <option value="done" <?= ($filters['status'] ?? '') === 'done' ? 'selected' : '' ?>>Виконано</option>
            </select>
        </div>
        <div>
            <label for="priority">Пріоритет</label>
            <select id="priority" name="priority">
                <option value="">Усі</option>
                <option value="low" <?= ($filters['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Низький</option>
                <option value="normal" <?= ($filters['priority'] ?? '') === 'normal' ? 'selected' : '' ?>>Звичайний</option>
                <option value="high" <?= ($filters['priority'] ?? '') === 'high' ? 'selected' : '' ?>>Високий</option>
                <option value="critical" <?= ($filters['priority'] ?? '') === 'critical' ? 'selected' : '' ?>>Критичний</option>
            </select>
        </div>
        <button type="submit" class="btn btn-sm">Фільтрувати</button>
        <a href="<?= url('/tasks') ?>" class="btn btn-sm">Скинути</a>
    </form>
</div>

<!-- Список завдань -->
<?php if (empty($tasks)): ?>
    <div class="card" style="text-align: center; padding: 3rem;">
        <p style="color: var(--text-muted);">Завдань не знайдено.</p>
    </div>
<?php else: ?>
    <div class="card" style="padding: 0; overflow: hidden;">
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
                    <tr>
                        <td data-label="Код">
                            <a href="<?= url('/tasks/' . ($task['public_id'] ?? '')) ?>" style="font-family: var(--font-mono); font-size: 0.8125rem;">
                                <?= e($task['code'] ?? '') ?>
                            </a>
                        </td>
                        <td data-label="Назва">
                            <a href="<?= url('/tasks/' . ($task['public_id'] ?? '')) ?>" style="font-weight: 500;">
                                <?= e($task['title'] ?? '') ?>
                            </a>
                        </td>
                        <td data-label="Пріоритет">
                            <span class="badge badge-<?= $task['priority'] === 'critical' ? 'danger' : ($task['priority'] === 'high' ? 'warning' : 'info') ?>">
                                <?= e($task['priority'] ?? '') ?>
                            </span>
                        </td>
                        <td data-label="Статус">
                            <?php
                            $statusLabel = match($task['status']) {
                                'new' => 'Нове',
                                'in_progress' => 'В роботі',
                                'on_hold' => 'На паузі',
                                'review' => 'На перевірці',
                                'done' => 'Виконано',
                                'cancelled' => 'Скасовано',
                                default => $task['status'] ?? '',
                            };
                            $isOverdue = $task['due_at'] && strtotime($task['due_at']) < time() && !in_array($task['status'], ['done', 'cancelled']);
                            ?>
                            <span class="badge badge-<?= $isOverdue ? 'danger' : ($task['status'] === 'done' ? 'success' : 'info') ?>">
                                <?= e($statusLabel) ?>
                                <?php if ($isOverdue): ?> ⏰<?php endif; ?>
                            </span>
                        </td>
                        <td data-label="Строк">
                            <?php if ($task['due_at']): ?>
                                <span style="<?= $isOverdue ? 'color: var(--danger);' : '' ?>">
                                    <?= dt($task['due_at'], 'd.m.Y') ?>
                                </span>
                            <?php else: ?>
                                <span style="color: var(--text-muted);">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php
\App\Core\View::endSection();
