<?php declare(strict_types=1);
\App\Core\View::startSection('content');
$isOwner = Auth::user() && Auth::user()['role_slug'] === 'owner';
$isSelf = Auth::id() === ($member['id'] ?? 0);
?>
<div style="margin-bottom:24px"><a href="<?= url('/team') ?>" style="color:var(--mute);font-size:13px">← Команда</a></div>
<div class="head">
  <div style="display:flex;align-items:center;gap:16px">
    <div style="width:56px;height:56px;border-radius:50%;background:var(--panel-2);display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:600;color:var(--accent)"><?= e(mb_substr($member['display_name'] ?? '?', 0, 1)) ?></div>
    <div>
      <h1 style="margin-bottom:4px"><?= e($member['display_name'] ?? '') ?></h1>
      <p><?= e($member['role_title'] ?? '') ?> · Остання активність: <?= time_ago($member['last_activity_at'] ?? null) ?></p>
    </div>
  </div>
  <?php if ($isOwner || $isSelf): ?>
  <a href="<?= url('/team/' . ($member['public_id'] ?? '') . '/edit') ?>" class="btn ghost sm">Редагувати профіль</a>
  <?php endif; ?>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:40px">
  <section>
    <h3 style="font:500 13.5px 'Manrope';color:var(--mute);margin:0 0 12px">Категорії та обов'язки</h3>
    <?php if (empty($categories)): ?>
      <div class="empty">Немає категорій</div>
    <?php else: ?>
    <div style="border:1px solid var(--line);border-radius:var(--r)">
      <?php foreach ($categories as $c): ?>
      <div style="padding:16px;border-bottom:1px solid var(--line)">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
          <span><?= $c['icon'] ?? '' ?></span>
          <strong style="font-size:13px"><?= e($c['title'] ?? '') ?></strong>
          <span class="badge badge-<?= $c['role_in_category']==='lead'?'warn':'accent' ?>" style="margin-left:auto"><?= e($c['role_in_category']) ?></span>
        </div>
        <?php if ($c['responsibilities'] ?? ''): ?>
        <div style="font-size:12px;color:var(--text);line-height:1.6;margin-top:8px;padding:10px;background:var(--panel);border-radius:var(--r-sm)"><?= nl2br(e($c['responsibilities'])) ?></div>
        <?php endif; ?>
        <div style="margin-top:8px;font-size:11px;color:var(--mute)">Навантаження: <?= str_repeat('●', (int)($c['workload'] ?? 0)) ?><?= str_repeat('○', 5 - (int)($c['workload'] ?? 0)) ?></div>
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
