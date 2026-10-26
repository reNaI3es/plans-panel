<?php
declare(strict_types=1);
\App\Core\View::startSection('content');
?>

<div style="margin-bottom: 1.5rem;">
    <a href="<?= url('/tasks') ?>" style="color: var(--text-dim); font-size: 0.875rem;">← Усі завдання</a>
</div>

<div style="display: grid; grid-template-columns: 1fr 320px; gap: 1.5rem;">
    <!-- Основна інформація -->
    <div>
        <div class="card" style="margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                <div>
                    <div style="font-family: var(--font-mono); font-size: 0.8125rem; color: var(--text-dim); margin-bottom: 0.25rem;">
                        <?= e($task['code'] ?? '') ?>
                    </div>
                    <h1 style="font-size: 1.5rem; font-weight: 700;"><?= e($task['title'] ?? '') ?></h1>
                </div>
                <span class="badge badge-<?= $task['priority'] === 'critical' ? 'danger' : ($task['priority'] === 'high' ? 'warning' : 'info') ?>">
                    <?= e($task['priority'] ?? '') ?>
                </span>
            </div>

            <?php if ($task['description'] ?? ''): ?>
                <div style="margin-top: 1rem; line-height: 1.6; color: var(--text-dim);">
                    <?= nl2br(e($task['description'])) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Чеклист -->
        <?php if (!empty($checklist)): ?>
        <div class="card" style="margin-bottom: 1.5rem;">
            <h2 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">📋 Чеклист</h2>
            <?php foreach ($checklist as $item): ?>
                <div style="padding: 0.5rem 0; display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" <?= $item['is_done'] ? 'checked' : '' ?> disabled>
                    <span style="<?= $item['is_done'] ? 'text-decoration: line-through; color: var(--text-muted);' : '' ?>">
                        <?= e($item['title'] ?? '') ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Коментарі -->
        <div class="card">
            <h2 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">💬 Коментарі (<?= count($comments) ?>)</h2>

            <?php foreach ($comments as $comment): ?>
                <div style="padding: 0.75rem 0; border-bottom: 1px solid var(--line);">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <div style="width: 28px; height: 28px; border-radius: 50%; background: var(--accent); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 600; color: #fff;">
                            <?= e($comment['avatar_initials'] ?? '?') ?>
                        </div>
                        <span style="font-weight: 500; font-size: 0.875rem;"><?= e($comment['display_name'] ?? '') ?></span>
                        <span style="font-size: 0.75rem; color: var(--text-muted);"><?= time_ago($comment['created_at'] ?? null) ?></span>
                    </div>
                    <div style="font-size: 0.875rem; color: var(--text-dim); line-height: 1.5;">
                        <?= nl2br(e($comment['body'] ?? '')) ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Форма додавання коментаря -->
            <form method="POST" action="<?= url('/tasks/' . ($task['public_id'] ?? '') . '/comment') ?>" style="margin-top: 1rem;">
                <?= csrf_field() ?>
                <div class="form-group">
                    <textarea name="body" rows="3" placeholder="Додати коментар..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Надіслати</button>
            </form>
        </div>
    </div>

    <!-- Бічна панель -->
    <div>
        <!-- Статус -->
        <div class="card" style="margin-bottom: 1rem;">
            <h3 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.75rem;">Статус</h3>
            <form method="POST" action="<?= url('/tasks/' . ($task['public_id'] ?? '') . '/status') ?>">
                <?= csrf_field() ?>
                <select name="status" style="margin-bottom: 0.75rem;">
                    <?php
                    $statuses = ['new' => 'Нове', 'in_progress' => 'В роботі', 'on_hold' => 'На паузі', 'review' => 'На перевірці', 'done' => 'Виконано', 'cancelled' => 'Скасовано'];
                    foreach ($statuses as $val => $label):
                    ?>
                        <option value="<?= $val ?>" <?= ($task['status'] ?? '') === $val ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-sm" style="width: 100%;">Оновити</button>
            </form>
        </div>

        <!-- Деталі -->
        <div class="card">
            <h3 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.75rem;">Деталі</h3>
            <div style="font-size: 0.8125rem;">
                <div style="padding: 0.375rem 0; display: flex; justify-content: space-between;">
                    <span style="color: var(--text-dim);">Тип:</span>
                    <span><?= e($task['type'] ?? '') ?></span>
                </div>
                <div style="padding: 0.375rem 0; display: flex; justify-content: space-between;">
                    <span style="color: var(--text-dim);">Категорія:</span>
                    <span><?= e($task['category_title'] ?? '—') ?></span>
                </div>
                <div style="padding: 0.375rem 0; display: flex; justify-content: space-between;">
                    <span style="color: var(--text-dim);">Строк:</span>
                    <span><?= dt($task['due_at'] ?? null, 'd.m.Y H:i') ?></span>
                </div>
                <div style="padding: 0.375rem 0; display: flex; justify-content: space-between;">
                    <span style="color: var(--text-dim);">Створив:</span>
                    <span><?= e($task['creator_name'] ?? '') ?></span>
                </div>
                <div style="padding: 0.375rem 0; display: flex; justify-content: space-between;">
                    <span style="color: var(--text-dim);">Створено:</span>
                    <span><?= dt($task['created_at'] ?? null, 'd.m.Y') ?></span>
                </div>
            </div>
        </div>

        <!-- Історія -->
        <?php if (!empty($history)): ?>
        <div class="card" style="margin-top: 1rem;">
            <h3 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.75rem;">Історія</h3>
            <?php foreach (array_slice($history, 0, 5) as $h): ?>
                <div style="font-size: 0.75rem; padding: 0.25rem 0; color: var(--text-dim);">
                    <?= time_ago($h['created_at'] ?? null) ?> — <?= e($h['display_name'] ?? '') ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
\App\Core\View::endSection();
