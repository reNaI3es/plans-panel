<?php declare(strict_types=1);
\App\Core\View::startSection('content');
?>
<div style="margin-bottom:24px"><a href="<?= url('/kb') ?>" style="color:var(--mute);font-size:13px">← База знань</a></div>
<div class="head">
  <div>
    <h1><?= e($article['title'] ?? '') ?></h1>
    <p><?= e($article['section_title'] ?? '') ?> · Автор: <?= e($article['author_name'] ?? '') ?> · Оновлено: <?= dt($article['updated_at'] ?? null, 'd.m.Y') ?> · Переглядів: <?= (int)($article['views'] ?? 0) ?></p>
  </div>
</div>
<div style="max-width:720px;line-height:1.8;font-size:14.5px">
<?= nl2br(e($article['body'] ?? '')) ?>
</div>
<?php \App\Core\View::endSection(); ?>
