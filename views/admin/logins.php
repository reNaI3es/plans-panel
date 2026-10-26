<?php declare(strict_types=1);
\App\Core\View::startSection('content');
?>
<div class="head">
  <div><h1>Журнал входів</h1><p>Історія спроб входу.</p></div>
</div>
<table class="tbl">
  <thead><tr><th>Час</th><th>Користувач</th><th>Логін</th><th>Результат</th><th>Причина</th></tr></thead>
  <tbody>
  <?php foreach ($logs as $l): ?>
  <tr>
    <td style="font-size:12px;color:var(--mute);white-space:nowrap"><?= dt($l['created_at'] ?? null, 'd.m.Y H:i') ?></td>
    <td><?= e($l['display_name'] ?? '—') ?></td>
    <td><span style="font-family:monospace;font-size:12px"><?= e($l['login_attempted'] ?? '') ?></span></td>
    <td><span class="badge badge-<?= $l['success'] ? 'ok' : 'bad' ?>"><?= $l['success'] ? 'Успіх' : 'Невдача' ?></span></td>
    <td style="font-size:12px;color:var(--mute)"><?= e($l['reason'] ?? '') ?></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php \App\Core\View::endSection(); ?>
