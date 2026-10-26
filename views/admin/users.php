<?php declare(strict_types=1);
\App\Core\View::startSection('content');
?>
<div class="head">
  <div><h1>Користувачі</h1><p>Керування доступами та ролями.</p></div>
  <a href="<?= url('/admin/users/create') ?>" class="btn"><svg class="ico" style="width:15px;height:15px"><use href="#i-plus"/></svg>Новий</a>
</div>
<div class="table-wrap">
<table class="tbl">
  <thead><tr><th>Логін</th><th>Ім'я</th><th>Роль</th><th>Статус</th><th>Створено</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($users as $u): ?>
  <tr>
    <td><span style="font-family:monospace;font-size:12px"><?= e($u['login']) ?></span></td>
    <td><a href="<?= url('/team/' . ($u['public_id'] ?? '')) ?>" style="color:var(--text);font-weight:500"><?= e($u['display_name']) ?></a></td>
    <td><span class="badge badge-accent"><?= e($u['role_title'] ?? '') ?></span></td>
    <td><span class="badge badge-<?= $u['status']==='active'?'ok':($u['status']==='suspended'?'bad':'warn') ?>"><?= e($u['status']) ?></span></td>
    <td style="font-size:12px;color:var(--mute)"><?= dt($u['created_at'] ?? null, 'd.m.Y') ?></td>
    <td style="text-align:right">
      <a href="<?= url('/admin/users/' . ($u['public_id'] ?? '') . '/edit') ?>" class="btn ghost sm">Редагувати</a>
    </td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php \App\Core\View::endSection(); ?>
