<?php declare(strict_types=1);
\App\Core\View::startSection('content');
?>
<div style="margin-bottom:24px"><a href="<?= url('/kb') ?>" style="color:var(--mute);font-size:13px">← База знань</a></div>
<div class="head">
  <div><h1>Пошук</h1></div>
</div>
<form method="GET" action="<?= url('/kb/search') ?>" style="margin-bottom:24px">
  <div style="display:flex;gap:8px">
    <input type="text" name="q" value="<?= e($query ?? '') ?>" placeholder="Що шукаємо?" style="flex:1">
    <button type="submit" class="btn sm">Шукати</button>
  </div>
</form>
<?php if (!empty($results)): ?>
<div style="border:1px solid var(--line);border-radius:var(--r)">
<?php foreach ($results as $a): ?>
<a href="<?= url('/kb/article/' . ($a['public_id'] ?? '')) ?>" class="task">
  <b><?= e($a['title'] ?? '') ?></b>
  <small><span><?= e($a['section_title'] ?? '') ?></span><span><?= dt($a['updated_at'] ?? '', 'd.m.Y') ?></span></small>
</a>
<?php endforeach; ?>
</div>
<?php elseif ($query ?? ''): ?>
<div class="empty">Нічого не знайдено за запитом «<?= e($query) ?>»</div>
<?php endif; ?>
<?php \App\Core\View::endSection(); ?>
