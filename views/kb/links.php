<?php declare(strict_types=1);
\App\Core\View::startSection('content');
?>
<div style="margin-bottom:24px"><a href="<?= url('/kb') ?>" style="color:var(--mute);font-size:13px">← База знань</a></div>
<div class="head"><div><h1>Посилання</h1><p>Корисні ресурси та інструменти.</p></div></div>
<?php foreach ($grouped as $group => $links): ?>
<h3 style="font:500 13.5px 'Manrope';color:var(--mute);margin:24px 0 12px"><?= e($group) ?></h3>
<div style="border:1px solid var(--line);border-radius:var(--r)">
<?php foreach ($links as $l): ?>
<div style="padding:12px 16px;border-bottom:1px solid var(--line);display:flex;align-items:center;gap:12px">
  <?php if ($l['is_secret']): ?>
  <svg class="ico" style="width:16px;height:16px;color:var(--warn)"><path d="M12 2L4 6v6c0 5 3.5 9.5 8 10 4.5-.5 8-5 8-10V6l-8-4z"/></svg>
  <?php endif; ?>
  <a href="<?= e($l['url']) ?>" target="_blank" rel="noopener" style="color:var(--text);font-weight:500;flex:1"><?= e($l['title']) ?></a>
  <?php if ($l['note'] ?? ''): ?><span style="font-size:12px;color:var(--mute)"><?= e($l['note']) ?></span><?php endif; ?>
</div>
<?php endforeach; ?>
</div>
<?php endforeach; ?>
<?php \App\Core\View::endSection(); ?>
