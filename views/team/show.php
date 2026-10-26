<?php declare(strict_types=1);
\App\Core\View::startSection('content');
?>
<div style="margin-bottom:24px"><a href="<?= url('/team') ?>" style="color:var(--mute);font-size:13px">← Команда</a></div>
<div class="head">
  <div>
    <h1><?= e($member['display_name'] ?? '') ?></h1>
    <p><?= e($member['role_title'] ?? '') ?> · Остання активність: <?= time_ago($member['last_activity_at'] ?? null) ?></p>
  </div>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:40px">
  <section>
    <h3 style="font:500 13.5px 'Manrope';color:var(--mute);margin:0 0 12px">Категорії</h3>
    <?php if (empty($categories)): ?>
      <div class="empty">Немає категорій</div>
    <?php else: ?>
    <div style="border:1px solid var(--line);border-radius:var(--r)">
      <?php foreach ($categories as $c): ?>
      <div style="padding:12px 16px;border-bottom:1px solid var(--line)">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
          <span><?= $c['icon'] ?? '📁' ?></span>
          <strong style="font-size:13px"><?= e($c['title'] ?? '') ?></strong>
          <span class="badge badge-accent" style="margin-left:auto"><?= e($c['role_in_category'] ?? '') ?></span>
        </div>
        <?php if ($c['responsibilities'] ?? ''): ?>
        <p style="font-size:12px;color:var(--mute);line-height:1.5"><?= e(str_limit($c['responsibilities'], 100)) ?></p>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </section>
  <section>
    <h3 style="font:500 13.5px 'Manrope';color:var(--mute);margin:0 0 12px">Завдання</h3>
    <?php if (empty($tasks)): ?>
      <div class="empty">Немає завдань</div>
    <?php else: ?>
    <div style="border:1px solid var(--line);border-radius:var(--r)">
      <?php foreach (array_slice($tasks, 0, 10) as $t): ?>
      <a href="<?= url('/tasks/' . ($t['public_id'] ?? '')) ?>" class="task">
        <b><?= e($t['title'] ?? '') ?></b>
        <small><span class="badge badge-<?= $t['priority']==='critical'?'bad':($t['priority']==='high'?'warn':'accent') ?>"><?= e($t['priority']) ?></span><span><?= dt($t['due_at'] ?? '', 'd.m.Y') ?></span></small>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </section>
</div>
<?php \App\Core\View::endSection(); ?>
