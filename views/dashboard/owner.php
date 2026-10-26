<?php declare(strict_types=1);
\App\Core\View::startSection('content');
?>
<div class="head">
  <div>
    <h1>Цього тижня</h1>
    <p>Натисни на завдання, щоб перемістити його далі.</p>
  </div>
  <div class="sh inl" style="--z:72px" aria-hidden="true"><div class="ring" style="--d:22s"><i></i><i></i><i></i><i></i></div></div>
  <div class="sprint">
    <div><span>Прогрес тижня</span><b><?= (int)($stats['done_pct'] ?? 0) ?>%</b></div>
    <div class="track"><i style="width:<?= (int)($stats['done_pct'] ?? 0) ?>%"></i></div>
  </div>
</div>

<section class="board">
  <?php
  $cols = [
    'todo' => ['label' => 'Заплановано', 'statuses' => ['new', 'on_hold']],
    'doing' => ['label' => 'У роботі', 'statuses' => ['in_progress', 'review']],
    'done' => ['label' => 'Готово', 'statuses' => ['done']],
  ];
  foreach ($cols as $key => $col):
    $colTasks = array_values(array_filter($tasks ?? [], function($t) use ($col) { return in_array($t['status'] ?? '', $col['statuses']); }));
  ?>
  <div class="col" data-s="<?= $key ?>">
    <h2><span><?= $col['label'] ?></span><em><?= count($colTasks) ?></em></h2>
    <?php if (empty($colTasks)): ?>
      <div class="empty">Порожньо</div>
    <?php else: foreach (array_slice($colTasks, 0, 8) as $t): ?>
      <a href="<?= url('/tasks/' . ($t['public_id'] ?? '')) ?>" class="task">
        <b><?= e($t['title'] ?? '') ?></b>
        <small>
          <span><?= e($t['category_title'] ?? '') ?></span>
          <span><?= $t['due_at'] ? dt($t['due_at'], 'd.m') : '' ?></span>
        </small>
        <span class="go">→</span>
      </a>
    <?php endforeach; endif; ?>
  </div>
  <?php endforeach; ?>
</section>

<div class="lower">
  <section>
    <div class="sh" style="--z:56px" aria-hidden="true"><div class="cube3" style="--d:18s"><b></b><b></b><b></b><b></b><b></b><b></b></div></div>
    <h3>Сервери</h3>
    <?php if (empty($servers ?? [])): ?>
      <div class="empty">Сервери не додано</div>
    <?php else: foreach (array_slice($servers ?? [], 0, 5) as $srv): ?>
    <div class="srv">
      <span class="d <?= ($srv['last_status'] ?? 'unknown') !== 'online' ? 'off' : '' ?>"></span>
      <span><?= e($srv['name'] ?? '') ?></span>
      <?php if ($srv['players_online'] ?? null): ?>
        <div class="bar2"><i style="width:<?= $srv['players_max'] ? round($srv['players_online'] / $srv['players_max'] * 100) : 0 ?>%"></i></div>
        <span><?= (int)$srv['players_online'] ?>/<?= (int)$srv['players_max'] ?></span>
      <?php else: ?>
        <span><?= ($srv['last_status'] ?? 'unknown') === 'online' ? 'Онлайн' : 'Офлайн' ?></span>
      <?php endif; ?>
    </div>
    <?php endforeach; endif; ?>
  </section>
  <section>
    <div class="sh" style="--z:60px" aria-hidden="true"><div class="plates" style="--d:16s"><i></i><i></i><i></i></div></div>
    <h3>Команда</h3>
    <ul class="ppl">
      <?php if (empty($team ?? [])): ?>
        <li><span class="empty">Учасників немає</span></li>
      <?php else: foreach (array_slice($team ?? [], 0, 6) as $m): ?>
      <li>
        <span class="av"><?= e(mb_substr($m['display_name'] ?? '?', 0, 1)) ?></span>
        <a href="<?= url('/team/' . ($m['public_id'] ?? '')) ?>" style="color:var(--text)"><?= e($m['display_name'] ?? '') ?></a>
        <span><?= e($m['role_title'] ?? '') ?></span>
      </li>
      <?php endforeach; endif; ?>
    </ul>
  </section>
</div>
<?php
\App\Core\View::endSection();
