<?php declare(strict_types=1);
\App\Core\View::startSection('content');
?>
<div class="head">
  <div><h1>Користувачі</h1><p>Керування доступами.</p></div>
</div>
<table class="tbl">
  <thead><tr><th>Логін</th><th>Ім'я</th><th>Роль</th><th>Статус</th><th>Створено</th></tr></thead>
  <tbody>
  <?php foreach ($users as $u): ?>
  <tr>
    <td><span style="font-family:monospace"><?= e($u['login']) ?></span></td>
    <td><a href="<?= url('/team/' . ($u['public_id'] ?? '')) ?>" style="color:var(--text);font-weight:500"><?= e($u['display_name']) ?></a></td>
    <td><span class="badge badge-accent"><?= e($u['role_title'] ?? '') ?></span></td>
    <td><span class="badge badge-<?= $u['status']==='active'?'ok':($u['status']==='suspended'?'bad':'warn') ?>"><?= e($u['status']) ?></span></td>
    <td style="font-size:12px;color:var(--mute)"><?= dt($u['created_at'] ?? null, 'd.m.Y') ?></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php \App\Core\View::endSection(); ?>
