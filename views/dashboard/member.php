<?php declare(strict_types=1);
\App\Core\View::startSection('content');
?>
<div class="head">
  <div>
    <h1>Вітаю, <span style="color:var(--accent)"><?= e($user['display_name'] ?? '') ?></span></h1>
    <p>Твої завдання та обов'язки на сьогодні.</p>
  </div>
</div>

<?php if (!empty($urgent ?? [])): ?>
<section style="margin-bottom:32px">
  <h3 style="color:var(--bad);font:500 13.5px 'Manrope';margin:0 0 12px">Термінове</h3>
  <div style="border:1px solid var(--line);border-radius:var(--r)">
    <?php foreach ($urgent as $t): ?>
    <a href="<?= url('/tasks/' . ($t['public_id'] ?? '')) ?>" class="task">
      <b><?= e($t['title'] ?? '') ?></b>
      <small><span>Строк: <?= dt($t['due_at'] ?? '', 'd.m.Y H:i') ?></span></small>
    </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($myCategories ?? [])): ?>
<section style="margin-bottom:32px">
  <h3 style="font:500 13.5px 'Manrope';color:var(--mute);margin:0 0 12px">Мої обов'язки</h3>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px">
    <?php foreach ($myCategories as $cat): ?>
    <div style="border:1px solid var(--line);border-radius:var(--r);padding:16px">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
        <span style="font-size:18px"><?= $cat['icon'] ?? '📁' ?></span>
        <strong style="font-size:14px"><?= e($cat['title'] ?? '') ?></strong>
        <span class="badge badge-accent" style="margin-left:auto"><?= e($cat['role_in_category'] ?? '') ?></span>
      </div>
      <?php if ($cat['duty_text'] ?? ''): ?>
      <p style="font-size:12px;color:var(--mute);line-height:1.5"><?= e(str_limit($cat['duty_text'], 100)) ?></p>
      <?php endif; ?>
      <div style="margin-top:8px;font-size:11px;color:var(--mute)">Активних: <?= (int)($cat['active_count'] ?? 0) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section>
  <h3 style="font:500 13.5px 'Manrope';color:var(--mute);margin:0 0 12px">Мої завдання</h3>
  <?php if (empty($myTasks ?? [])): ?>
    <div class="empty">Немає активних завдань</div>
  <?php else: ?>
  <div style="border:1px solid var(--line);border-radius:var(--r)">
    <?php foreach (array_slice($myTasks ?? [], 0, 10) as $t): ?>
    <a href="<?= url('/tasks/' . ($t['public_id'] ?? '')) ?>" class="task">
      <b><?= e($t['title'] ?? '') ?></b>
      <small>
        <span class="badge badge-<?= $t['priority'] === 'critical' ? 'bad' : ($t['priority'] === 'high' ? 'warn' : 'accent') ?>"><?= e($t['priority'] ?? '') ?></span>
        <span><?= dt($t['due_at'] ?? '', 'd.m.Y') ?></span>
      </small>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>
<?php
\App\Core\View::endSection();
