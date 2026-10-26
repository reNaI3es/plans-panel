<?php declare(strict_types=1);
\App\Core\View::startSection('content');
?>
<div style="margin-bottom:24px"><a href="<?= url('/tasks') ?>" style="color:var(--mute);font-size:13px">← Завдання</a></div>
<div class="head"><div><h1>Нове завдання</h1></div></div>
<form method="POST" action="<?= url('/tasks/create') ?>" style="max-width:640px">
  <?= csrf_field() ?>
  <div class="f"><label>Назва *</label><input type="text" name="title" required maxlength="200"></div>
  <div class="f"><label>Опис</label><textarea name="description" rows="5"></textarea></div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
    <div class="f"><label>Тип</label><select name="type"><option value="additional">Додаткове</option><option value="primary">Основне</option><option value="urgent">Термінове</option><option value="routine">Регулярне</option></select></div>
    <div class="f"><label>Пріоритет</label><select name="priority"><option value="normal">Звичайний</option><option value="low">Низький</option><option value="high">Високий</option><option value="critical">Критичний</option></select></div>
  </div>
  <div class="f"><label>Категорія</label><select name="category_id"><option value="">— Не вказано —</option><?php foreach ($categories as $c): ?><option value="<?= (int)$c['id'] ?>"><?= e($c['title']) ?></option><?php endforeach; ?></select></div>
  <div class="f"><label>Виконавці</label><div style="max-height:200px;overflow-y:auto;border:1px solid var(--line);border-radius:var(--r-sm);padding:8px"><?php foreach ($users as $u): ?><label style="display:flex;align-items:center;gap:8px;padding:4px 0;cursor:pointer"><input type="checkbox" name="assign_users[]" value="<?= (int)$u['id'] ?>" style="width:auto"><span style="font-size:13px"><?= e($u['display_name']) ?></span></label><?php endforeach; ?></div></div>
  <div class="f"><label>Строк</label><input type="datetime-local" name="due_at"></div>
  <div class="f"><label style="display:flex;align-items:center;gap:8px;cursor:pointer"><input type="checkbox" name="require_review" value="1" checked style="width:auto"><span style="font-size:13px">Потрібна перевірка</span></label></div>
  <div style="display:flex;gap:8px;margin-top:24px"><button type="submit" class="btn">Створити</button><a href="<?= url('/tasks') ?>" class="btn ghost">Скасувати</a></div>
</form>
<?php \App\Core\View::endSection(); ?>
