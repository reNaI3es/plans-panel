<?php declare(strict_types=1);
\App\Core\View::startSection('content');
?>
<div class="head">
  <div><h1>Матриця відповідальності</h1><p>Хто за що відповідає.</p></div>
  <a href="<?= url('/team') ?>" class="btn ghost sm">← Команда</a>
</div>
<div style="overflow-x:auto">
<table class="tbl">
  <thead>
    <tr>
      <th style="min-width:140px">Учасник</th>
      <?php foreach ($categories as $c): ?>
      <th style="text-align:center;min-width:80px"><?= e($c['icon'] ?? '') ?><br><span style="font-size:10px"><?= e(str_limit($c['title'], 10)) ?></span></th>
      <?php endforeach; ?>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($members as $m): ?>
  <tr>
    <td><a href="<?= url('/team/' . ($m['public_id'] ?? '')) ?>" style="color:var(--text);font-weight:500"><?= e($m['display_name'] ?? '') ?></a><br><span style="font-size:11px;color:var(--mute)">Активних: <?= (int)($m['active_tasks'] ?? 0) ?> · Прострочено: <?= (int)($m['overdue_tasks'] ?? 0) ?></span></td>
    <?php foreach ($categories as $c):
      $cell = $matrix[$m['id']][$c['id']] ?? null;
    ?>
    <td style="text-align:center">
      <?php if ($cell): ?>
        <span class="badge badge-<?= $cell['role_in_category'] === 'lead' ? 'warn' : 'accent' ?>"><?= e($cell['role_in_category']) ?></span>
      <?php else: ?>
        <span style="color:var(--line-2)">—</span>
      <?php endif; ?>
    </td>
    <?php endforeach; ?>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php \App\Core\View::endSection(); ?>
