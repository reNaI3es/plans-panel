<?php
declare(strict_types=1);
/**
 * Дашборд учасника (member) — першим ділом бачить своє.
 */
\App\Core\View::startSection('content');
?>

<h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem;">
    Привіт, <?= e($user['display_name'] ?? '') ?> 👋
</h1>

<!-- Термінове -->
<?php if (!empty($urgent)): ?>
<div class="card" style="border-color: var(--danger); margin-bottom: 1.5rem;">
    <h2 style="font-size: 1rem; font-weight: 600; color: var(--danger); margin-bottom: 1rem;">🔥 Термінове</h2>
    <?php foreach ($urgent as $task): ?>
        <div style="padding: 0.75rem 0; border-bottom: 1px solid var(--line);">
            <a href="<?= url('/tasks/' . ($task['public_id'] ?? '')) ?>" style="font-weight: 500;">
                <?= e($task['code'] ?? '') ?> — <?= e($task['title'] ?? '') ?>
            </a>
            <div style="font-size: 0.75rem; color: var(--text-dim); margin-top: 0.25rem;">
                <?php if ($task['due_at'] && strtotime($task['due_at']) < time()): ?>
                    <span style="color: var(--danger);">⏰ Прострочено <?= dt($task['due_at'], 'd.m.Y H:i') ?></span>
                <?php else: ?>
                    <span style="color: var(--warning);">⏰ До <?= dt($task['due_at'] ?? '', 'd.m.Y H:i') ?></span>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Мої обов'язки -->
<?php if (!empty($myCategories)): ?>
<div class="card" style="margin-bottom: 1.5rem;">
    <h2 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">📁 Мої обов'язки</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 0.75rem;">
        <?php foreach ($myCategories as $cat): ?>
            <div style="padding: 1rem; background: var(--bg); border: 1px solid var(--line); border-radius: var(--radius);">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <span><?= $cat['icon'] ?? '📁' ?></span>
                    <strong style="font-size: 0.875rem;"><?= e($cat['title'] ?? '') ?></strong>
                    <span class="badge badge-info" style="margin-left: auto;"><?= e($cat['role_in_category'] ?? '') ?></span>
                </div>
                <?php if ($cat['duty_text'] ?? ''): ?>
                    <p style="font-size: 0.75rem; color: var(--text-dim); line-height: 1.4;"><?= e(str_limit($cat['duty_text'], 120)) ?></p>
                <?php endif; ?>
                <div style="margin-top: 0.5rem; font-size: 0.75rem; color: var(--text-muted);">
                    Активних завдань: <?= (int)($cat['active_count'] ?? 0) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Мої завдання -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h2 style="font-size: 1rem; font-weight: 600;">✅ Мої завдання</h2>
        <a href="<?= url('/tasks') ?>" class="btn btn-sm">Усі →</a>
    </div>
    <?php if (empty($myTasks)): ?>
        <p style="color: var(--text-muted); font-size: 0.875rem;">Немає активних завдань 🎉</p>
    <?php else: ?>
        <?php foreach (array_slice($myTasks, 0, 10) as $task): ?>
            <div style="padding: 0.75rem 0; border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <a href="<?= url('/tasks/' . ($task['public_id'] ?? '')) ?>" style="font-weight: 500; font-size: 0.875rem;">
                        <?= e($task['code'] ?? '') ?> — <?= e($task['title'] ?? '') ?>
                    </a>
                    <div style="font-size: 0.75rem; color: var(--text-dim); margin-top: 0.25rem;">
                        <span class="badge badge-<?= $task['priority'] === 'critical' ? 'danger' : ($task['priority'] === 'high' ? 'warning' : 'info') ?>">
                            <?= e($task['priority'] ?? '') ?>
                        </span>
                        <span style="margin-left: 0.5rem;">Строк: <?= dt($task['due_at'] ?? '', 'd.m.Y') ?></span>
                    </div>
                </div>
                <span class="badge badge-<?= $task['status'] === 'done' ? 'success' : 'info' ?>">
                    <?= e($task['status'] ?? '') ?>
                </span>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Швидкі дії -->
<div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
    <a href="<?= url('/kb') ?>" class="btn">📚 База знань</a>
    <a href="<?= url('/servers') ?>" class="btn">🖥️ Сервери</a>
    <a href="<?= url('/ai') ?>" class="btn">🤖 ШІ-помічник</a>
    <a href="<?= url('/notifications') ?>" class="btn">🔔 Сповіщення</a>
</div>

<?php
\App\Core\View::endSection();
