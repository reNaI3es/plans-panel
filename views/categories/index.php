<?php declare(strict_types=1);
\App\Core\View::startSection('content');
?>
<div class="head">
  <div><h1>Категорії</h1><p>Напрямки діяльності команди.</p></div>
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px">
<?php foreach ($categories as $c): ?>
<a href="<?= url('/categories/' . ($c['public_id'] ?? '')) ?>" style="border:1px solid var(--line);border-radius:var(--r);padding:20px;text-decoration:none;color:var(--text);transition:border-color .15s" onmouseover="this.style.borderColor='var(--line-2)'" onmouseout="this.style.borderColor=''">
  <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
    <span style="font-size:24px"><?= $c['icon'] ?? '📁' ?></span>
    <strong style="font-size:15px"><?= e($c['title'] ?? '') ?></strong>
  </div>
  <?php if ($c['description'] ?? ''): ?>
  <p style="font-size:13px;color:var(--mute);line-height:1.5;margin-bottom:12px"><?= e(str_limit($c['description'], 80)) ?></p>
  <?php endif; ?>
  <div style="display:flex;gap:16px;font-size:12px;color:var(--mute)">
    <span><?= (int)($c['members_count'] ?? 0) ?> учасників</span>
    <span><?= (int)($c['active_tasks'] ?? 0) ?> завдань</span>
  </div>
</a>
<?php endforeach; ?>
</div>
<?php \App\Core\View::endSection(); ?>
