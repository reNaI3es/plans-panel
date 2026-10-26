<?php declare(strict_types=1);
\App\Core\View::startSection('content');
?>
<div class="head">
  <div><h1>Журнал дій</h1><p>Історія всіх операцій.</p></div>
</div>
<table class="tbl">
  <thead><tr><th>Час</th><th>Користувач</th><th>Дія</th><th>Об'єкт</th></tr></thead>
  <tbody>
  <?php foreach ($logs as $l): ?>
  <tr>
    <td style="font-size:12px;color:var(--mute);white-space:nowrap"><?= dt($l['created_at'] ?? null, 'd.m.Y H:i') ?></td>
    <td><?= e($l['display_name'] ?? 'Система') ?></td>
    <td><span style="font-family:monospace;font-size:12px"><?= e($l['action'] ?? '') ?></span></td>
    <td style="font-size:13px"><?= e($l['entity_label'] ?? '') ?></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php \App\Core\View::endSection(); ?>
