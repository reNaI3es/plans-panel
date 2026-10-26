<?php declare(strict_types=1);
\App\Core\View::startSection('content');
?>
<div class="head">
  <div><h1>Команда</h1><p>Усі учасники проєкту.</p></div>
  <?php if (\App\Core\Acl::can('team.matrix')): ?>
  <a href="<?= url('/team/matrix') ?>" class="btn ghost sm">Матриця відповідальності</a>
  <?php endif; ?>
</div>
<ul class="ppl" style="border:1px solid var(--line);border-radius:var(--r);padding:0 16px">
<?php foreach ($members as $m): ?>
<li>
  <span class="av"><?= e(mb_substr($m['display_name'] ?? '?', 0, 1)) ?></span>
  <a href="<?= url('/team/' . ($m['public_id'] ?? '')) ?>" style="color:var(--text);font-weight:500"><?= e($m['display_name'] ?? '') ?></a>
  <span><?= e($m['role_title'] ?? '') ?> · <?= (int)($m['active_tasks'] ?? 0) ?> завдань</span>
</li>
<?php endforeach; ?>
</ul>
<?php \App\Core\View::endSection(); ?>
