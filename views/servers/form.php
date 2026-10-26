<?php declare(strict_types=1);
\App\Core\View::startSection('content');
$isEdit = !empty($server);
?>
<div style="margin-bottom:24px"><a href="<?= url('/servers') ?>" style="color:var(--mute);font-size:13px">← Сервери</a></div>
<div class="head"><div><h1><?= $isEdit ? 'Редагування сервера' : 'Новий сервер' ?></h1></div></div>
<form method="POST" action="<?= $isEdit ? url('/servers/' . ($server['public_id'] ?? '') . '/edit') : url('/servers/create') ?>" style="max-width:640px">
  <?= csrf_field() ?>
  <div class="f"><label>Назва *</label><input type="text" name="name" value="<?= e($server['name'] ?? '') ?>" required></div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
    <div class="f"><label>Гра</label><select name="game"><option value="cs2" <?= ($server['game'] ?? '') === 'cs2' ? 'selected' : '' ?>>CS2</option><option value="csgo" <?= ($server['game'] ?? '') === 'csgo' ? 'selected' : '' ?>>CS:GO</option><option value="minecraft" <?= ($server['game'] ?? '') === 'minecraft' ? 'selected' : '' ?>>Minecraft</option><option value="unturned" <?= ($server['game'] ?? '') === 'unturned' ? 'selected' : '' ?>>Unturned</option><option value="rust" <?= ($server['game'] ?? '') === 'rust' ? 'selected' : '' ?>>Rust</option><option value="other" <?= ($server['game'] ?? '') === 'other' ? 'selected' : '' ?>>Інше</option></select></div>
    <div class="f"><label>Протокол</label><select name="protocol"><option value="source" <?= ($server['protocol'] ?? '') === 'source' ? 'selected' : '' ?>>Source</option><option value="minecraft" <?= ($server['protocol'] ?? '') === 'minecraft' ? 'selected' : '' ?>>Minecraft</option><option value="http" <?= ($server['protocol'] ?? '') === 'http' ? 'selected' : '' ?>>HTTP</option></select></div>
  </div>
  <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px">
    <div class="f"><label>Хост *</label><input type="text" name="host" value="<?= e($server['host'] ?? '') ?>" required placeholder="185.16.100.1"></div>
    <div class="f"><label>Порт *</label><input type="number" name="port" value="<?= e($server['port'] ?? '') ?>" required placeholder="27015"></div>
  </div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
    <div class="f"><label>Query порт</label><input type="number" name="query_port" value="<?= e($server['query_port'] ?? '') ?>" placeholder="27015"></div>
    <div class="f"><label>RCON порт</label><input type="number" name="rcon_port" value="<?= e($server['rcon_port'] ?? '') ?>" placeholder="27015"></div>
  </div>
  <div class="f"><label>RCON пароль</label><input type="password" name="rcon_password" value="" placeholder="<?= $isEdit ? 'Залиште порожнім щоб не змінювати' : '' ?>"></div>
  <div class="f"><label>Категорія</label><select name="category_id"><option value="">— Не вказано —</option><?php foreach ($categories as $c): ?><option value="<?= (int)$c['id'] ?>" <?= ($server['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['title']) ?></option><?php endforeach; ?></select></div>
  <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">
    <div class="f"><label>Порядок</label><input type="number" name="sort_order" value="<?= e($server['sort_order'] ?? '0') ?>"></div>
    <div class="f"><label style="display:flex;align-items:center;gap:8px;cursor:pointer"><input type="checkbox" name="is_public_visible" value="1" <?= ($server['is_public_visible'] ?? 1) ? 'checked' : '' ?> style="width:auto"><span>Публічний</span></label></div>
    <div class="f"><label style="display:flex;align-items:center;gap:8px;cursor:pointer"><input type="checkbox" name="monitor_enabled" value="1" <?= ($server['monitor_enabled'] ?? 1) ? 'checked' : '' ?> style="width:auto"><span>Моніторинг</span></label></div>
  </div>
  <div style="display:flex;gap:8px;margin-top:24px"><button type="submit" class="btn"><?= $isEdit ? 'Зберегти' : 'Додати' ?></button><a href="<?= url('/servers') ?>" class="btn ghost">Скасувати</a></div>
</form>
<?php \App\Core\View::endSection(); ?>
