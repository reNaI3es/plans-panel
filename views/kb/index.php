<?php declare(strict_types=1);
\App\Core\View::startSection('content');
?>
<div class="head">
  <div><h1>База знань</h1><p>Інструкції, регламенти, доступи.</p></div>
  <a href="<?= url('/kb/search') ?>" class="btn ghost sm">Пошук</a>
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;margin-bottom:40px">
<?php foreach ($sections as $s): ?>
<a href="<?= url('/kb') ?>#<?= e($s['slug']) ?>" style="border:1px solid var(--line);border-radius:var(--r);padding:20px;text-decoration:none;color:var(--text);transition:border-color .15s" onmouseover="this.style.borderColor='var(--line-2)'" onmouseout="this.style.borderColor=''">
  <div style="font-size:24px;margin-bottom:8px"><?= $s['icon'] ?? '📄' ?></div>
  <div style="font-weight:500;margin-bottom:4px"><?= e($s['title'] ?? '') ?></div>
</a>
<?php endforeach; ?>
</div>
<?php if (!empty($pinned)): ?>
<h3 style="font:500 13.5px 'Manrope';color:var(--mute);margin:0 0 12px">Закріплені статті</h3>
<div style="border:1px solid var(--line);border-radius:var(--r)">
<?php foreach ($pinned as $a): ?>
<a href="<?= url('/kb/article/' . ($a['public_id'] ?? '')) ?>" class="task">
  <b><?= e($a['title'] ?? '') ?></b>
  <small><span><?= e($a['section_title'] ?? '') ?></span></small>
</a>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php \App\Core\View::endSection(); ?>
