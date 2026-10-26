<?php
declare(strict_types=1);
\App\Core\View::startSection('content');
?>

<div style="margin-bottom: 1.5rem;">
    <a href="<?= url('/tasks') ?>" style="color: var(--text-dim); font-size: 0.875rem;">← Усі завдання</a>
</div>

<div class="card" style="max-width: 720px;">
    <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem;">Нове завдання</h1>

    <form method="POST" action="<?= url('/tasks/create') ?>">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="title">Назва *</label>
            <input type="text" id="title" name="title" required maxlength="200">
        </div>

        <div class="form-group">
            <label for="description">Опис</label>
            <textarea id="description" name="description" rows="5"></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="type">Тип</label>
                <select id="type" name="type">
                    <option value="additional">Додаткове</option>
                    <option value="primary">Основне</option>
                    <option value="urgent">Термінове</option>
                    <option value="routine">Регулярне</option>
                </select>
            </div>

            <div class="form-group">
                <label for="priority">Пріоритет</label>
                <select id="priority" name="priority">
                    <option value="normal">Звичайний</option>
                    <option value="low">Низький</option>
                    <option value="high">Високий</option>
                    <option value="critical">Критичний</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="category_id">Категорія</label>
            <select id="category_id" name="category_id">
                <option value="">— Не вказано —</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int)$cat['id'] ?>"><?= e($cat['icon'] ?? '') ?> <?= e($cat['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Призначити виконавцям</label>
            <div style="max-height: 200px; overflow-y: auto; border: 1px solid var(--line); border-radius: var(--radius); padding: 0.5rem;">
                <?php foreach ($users as $u): ?>
                    <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.375rem 0; cursor: pointer;">
                        <input type="checkbox" name="assign_users[]" value="<?= (int)$u['id'] ?>" style="width: auto;">
                        <span style="font-size: 0.875rem;"><?= e($u['display_name']) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="form-group">
            <label for="due_at">Строк виконання</label>
            <input type="datetime-local" id="due_at" name="due_at">
        </div>

        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" name="require_review" value="1" checked style="width: auto;">
                <span style="font-size: 0.875rem;">Потрібна перевірка перед виконанням</span>
            </label>
        </div>

        <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary">Створити завдання</button>
            <a href="<?= url('/tasks') ?>" class="btn">Скасувати</a>
        </div>
    </form>
</div>

<?php
\App\Core\View::endSection();
