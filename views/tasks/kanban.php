<?php declare(strict_types=1);
\App\Core\View::startSection('content');
?>
<div class="head">
  <div><h1>Канбан</h1><p>Перетягуй завдання між колонками.</p></div>
  <div style="display:flex;gap:8px">
    <a href="<?= url('/tasks') ?>" class="btn ghost sm">Список</a>
    <a href="<?= url('/tasks/calendar') ?>" class="btn ghost sm">Календар</a>
  </div>
</div>
<div class="board kanban-board">
<?php
$labels = ['new'=>'Заплановано','in_progress'=>'У роботі','on_hold'=>'На паузі','review'=>'На перевірці','done'=>'Готово'];
foreach ($columns as $status => $tasks):
?>
<div class="col kanban-column" data-status="<?= e($status) ?>">
  <h2><span><?= e($labels[$status] ?? $status) ?></span><em><?= count($tasks) ?></em></h2>
  <div class="kanban-list">
  <?php if (empty($tasks)): ?>
    <div class="empty">Порожньо</div>
  <?php else: foreach ($tasks as $t): ?>
    <div class="task kanban-card" data-id="<?= e($t['public_id'] ?? '') ?>" data-status-url="<?= url('/tasks/' . ($t['public_id'] ?? '') . '/status') ?>">
      <a href="<?= url('/tasks/' . ($t['public_id'] ?? '')) ?>" style="color:var(--text);text-decoration:none">
        <b><?= e($t['title'] ?? '') ?></b>
        <small>
          <span class="badge badge-<?= $t['priority']==='critical'?'bad':($t['priority']==='high'?'warn':'accent') ?>"><?= e($t['priority']) ?></span>
          <span><?= $t['due_at'] ? dt($t['due_at'], 'd.m') : '' ?></span>
        </small>
      </a>
    </div>
  <?php endforeach; endif; ?>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php \App\Core\View::endSection(); ?>
