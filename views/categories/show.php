<?php declare(strict_types=1);
\App\Core\View::startSection('content');
?>
<div style="margin-bottom:24px"><a href="<?= url('/categories') ?>" style="color:var(--mute);font-size:13px">← Категорії</a></div>
<div class="head">
  <div>
    <h1><?= $category['icon'] ?? '📁' ?> <?= e($category['title'] ?? '') ?></h1>
    <p><?= e($category['description'] ?? '') ?></p>
  </div>
</div>
<?php if ($category['duty_text'] ?? ''): ?>
<div style="border:1px solid var(--line);border-radius:var(--r);padding:16px;margin-bottom:32px">
  <h3 style="font:500 13.5px 'Manrope';color:var(--mute);margin:0 0 8px">Що тримати в порядку</h3>
  <p style="font-size:13px;line-height:1.6"><?= nl2br(e($category['duty_text'])) ?></p>
</div>
<?php endif; ?>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:40px">
  <section>
    <h3 style="font:500 13.5px 'Manrope';color:var(--mute);margin:0 0 12px">Учасники</h3>
    <ul class="ppl" style="border:1px solid var(--line);border-radius:var(--r);padding:0 16px">
    <?php if (empty($members)): ?>
      <li><span class="empty">Немає учасників</span></li>
    <?php else: foreach ($members as $m): ?>
    <li>
      <span class="av"><?= e(mb_substr($m['display_name'] ?? '?', 0, 1)) ?></span>
      <a href="<?= url('/team/' . ($m['public_id'] ?? '')) ?>" style="color:var(--text);font-weight:500"><?= e($m['display_name']) ?></a>
      <span class="badge badge-<?= $m['role_in_category']==='lead'?'warn':'accent' ?>"><?= e($m['role_in_category']) ?></span>
    </li>
    <?php endforeach; endif; ?>
    </ul>
  </section>
  <section>
    <h3 style="font:500 13.5px 'Manrope';color:var(--mute);margin:0 0 12px">Завдання</h3>
    <div style="border:1px solid var(--line);border-radius:var(--r)">
    <?php if (empty($tasks)): ?>
      <div class="empty">Немає завдань</div>
    <?php else: foreach (array_slice($tasks, 0, 10) as $t): ?>
    <a href="<?= url('/tasks/' . ($t['public_id'] ?? '')) ?>" class="task">
      <b><?= e($t['title'] ?? '') ?></b>
      <small><span class="badge badge-<?= $t['priority']==='critical'?'bad':($t['priority']==='high'?'warn':'accent') ?>"><?= e($t['priority']) ?></span><span><?= dt($t['due_at'] ?? '', 'd.m.Y') ?></span></small>
    </a>
    <?php endforeach; endif; ?>
    </div>
  </section>
</div>
<?php \App\Core\View::endSection(); ?>
