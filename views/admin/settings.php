<?php declare(strict_types=1);
\App\Core\View::startSection('content');
?>
<div class="head">
  <div><h1>Налаштування</h1><p>Керування системою.</p></div>
</div>
<form method="POST" action="<?= url('/admin/settings') ?>">
  <?= csrf_field() ?>
  <?php foreach ($grouped as $group => $settings): ?>
  <div style="margin-bottom:32px">
    <h3 style="font:500 13.5px 'Manrope';color:var(--mute);margin:0 0 12px;text-transform:uppercase;letter-spacing:.02em"><?= e($group) ?></h3>
    <div style="border:1px solid var(--line);border-radius:var(--r)">
      <?php foreach ($settings as $s):
        if ($s['is_secret'] && !$s['setting_value']) continue;
      ?>
      <div style="padding:12px 16px;border-bottom:1px solid var(--line);display:flex;align-items:center;gap:16px">
        <label style="min-width:200px;margin:0;font-size:13px"><?= e(str_replace('_', ' ', $s['setting_key'])) ?></label>
        <?php if ($s['is_secret']): ?>
          <input type="password" name="settings[<?= e($s['setting_key']) ?>]" value="" placeholder="••••••••" style="flex:1">
        <?php elseif ($s['value_type'] === 'bool'): ?>
          <select name="settings[<?= e($s['setting_key']) ?>]" style="flex:1">
            <option value="0" <?= !$s['setting_value'] ? 'selected' : '' ?>>Ні</option>
            <option value="1" <?= $s['setting_value'] ? 'selected' : '' ?>>Так</option>
          </select>
        <?php else: ?>
          <input type="text" name="settings[<?= e($s['setting_key']) ?>]" value="<?= e($s['setting_value'] ?? '') ?>" style="flex:1">
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>
  <button type="submit" class="btn">Зберегти налаштування</button>
</form>
<?php \App\Core\View::endSection(); ?>
