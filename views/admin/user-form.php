<?php declare(strict_types=1);
\App\Core\View::startSection('content');
$isEdit = !empty($user);
?>
<div style="margin-bottom:24px"><a href="<?= url('/admin/users') ?>" style="color:var(--mute);font-size:13px">← Користувачі</a></div>
<div class="head"><div><h1><?= $isEdit ? 'Редагування' : 'Новий користувач' ?></h1></div></div>
<form method="POST" action="<?= $isEdit ? url('/admin/users/' . ($user['public_id'] ?? '') . '/edit') : url('/admin/users/create') ?>" style="max-width:560px">
  <?= csrf_field() ?>
  <div class="f"><label>Логін *</label><input type="text" name="login" value="<?= e($user['login'] ?? '') ?>" required <?= $isEdit ? 'disabled' : '' ?>></div>
  <div class="f"><label>Відображуване ім'я *</label><input type="text" name="display_name" value="<?= e($user['display_name'] ?? '') ?>" required></div>
  <div class="f"><label>Роль *</label><select name="role_id" required><?php foreach ($roles as $r): ?><option value="<?= (int)$r['id'] ?>" <?= ($user['role_id'] ?? '') == $r['id'] ? 'selected' : '' ?>><?= e($r['title']) ?></option><?php endforeach; ?></select></div>
  <?php if ($isEdit): ?>
  <div class="f"><label>Статус</label><select name="status"><option value="active" <?= ($user['status'] ?? '') === 'active' ? 'selected' : '' ?>>Активний</option><option value="suspended" <?= ($user['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Призупинено</option><option value="deleted" <?= ($user['status'] ?? '') === 'deleted' ? 'selected' : '' ?>>Видалено</option></select></div>
  <?php endif; ?>
  <div class="f"><label>Пароль <?= $isEdit ? '(залиште порожнім щоб не змінювати)' : '*' ?></label><input type="password" name="password" <?= $isEdit ? '' : 'required' ?> minlength="10"></div>
  <div style="display:flex;gap:8px;margin-top:24px"><button type="submit" class="btn"><?= $isEdit ? 'Зберегти' : 'Створити' ?></button><a href="<?= url('/admin/users') ?>" class="btn ghost">Скасувати</a></div>
</form>
<?php \App\Core\View::endSection(); ?>
