<?php declare(strict_types=1);
\App\Core\View::startSection('content');
?>
<div class="head">
  <div><h1>Сповіщення</h1><p><?= (int)($unreadCount ?? 0) ?> непрочитаних</p></div>
  <?php if ($unreadCount > 0): ?>
  <form method="POST" action="<?= url('/notifications/read') ?>">
    <?= csrf_field() ?>
    <button type="submit" class="btn ghost sm">Позначити всі прочитаними</button>
  </form>
  <?php endif; ?>
</div>
<?php if (empty($notifications)): ?>
  <div class="empty">Немає сповіщень</div>
<?php else: ?>
<div style="border:1px solid var(--line);border-radius:var(--r)">
<?php foreach ($notifications as $n): ?>
<div style="padding:14px 16px;border-bottom:1px solid var(--line);display:flex;gap:12px;align-items:start;<?= !$n['is_read'] ? 'background:var(--panel)' : '' ?>">
  <span class="d <?= $n['severity'] === 'critical' ? 'off' : '' ?>" style="margin-top:6px"></span>
  <div style="flex:1">
    <div style="font-weight:500;margin-bottom:4px"><?= e($n['title'] ?? '') ?></div>
    <?php if ($n['body'] ?? ''): ?><div style="font-size:13px;color:var(--mute)"><?= e($n['body']) ?></div><?php endif; ?>
    <div style="font-size:11px;color:var(--mute);margin-top:4px"><?= time_ago($n['created_at'] ?? null) ?></div>
  </div>
  <?php if ($n['url'] ?? ''): ?>
  <a href="<?= url($n['url']) ?>" class="btn ghost sm">Відкрити</a>
  <?php endif; ?>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php \App\Core\View::endSection(); ?>
