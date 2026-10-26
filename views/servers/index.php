<?php declare(strict_types=1);
\App\Core\View::startSection('content');
?>
<div class="head">
  <div><h1>Сервери</h1><p>Моніторинг ігрових серверів.</p></div>
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px">
<?php if (empty($servers)): ?>
  <div class="empty">Сервери не додано</div>
<?php else: foreach ($servers as $s): ?>
<div style="border:1px solid var(--line);border-radius:var(--r);padding:20px">
  <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
    <span class="d <?= ($s['last_status'] ?? 'unknown') !== 'online' ? 'off' : '' ?>"></span>
    <strong style="font-size:15px"><?= e($s['name'] ?? '') ?></strong>
    <span class="badge badge-<?= ($s['last_status'] ?? '') === 'online' ? 'ok' : 'bad' ?>" style="margin-left:auto"><?= e($s['last_status'] ?? 'unknown') ?></span>
  </div>
  <div style="font-size:12px;color:var(--mute);display:flex;gap:16px;flex-wrap:wrap">
    <?php if ($s['players_online'] ?? null): ?>
    <span>Гравці: <?= (int)$s['players_online'] ?>/<?= (int)$s['players_max'] ?></span>
    <?php endif; ?>
    <?php if ($s['map_name'] ?? ''): ?><span>Карта: <?= e($s['map_name']) ?></span><?php endif; ?>
    <?php if ($s['ping_ms'] ?? null): ?><span>Пінг: <?= (int)$s['ping_ms'] ?>ms</span><?php endif; ?>
    <?php if ($canViewIp): ?><span><?= e($s['host'] ?? '') ?>:<?= (int)($s['port'] ?? 0) ?></span><?php endif; ?>
  </div>
</div>
<?php endforeach; endif; ?>
</div>
<?php \App\Core\View::endSection(); ?>
