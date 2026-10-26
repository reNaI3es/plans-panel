<?php declare(strict_types=1);
\App\Core\View::startSection('content');
?>
<div class="head">
  <div>
    <h1>Завдання</h1>
    <p>Усі завдання команди.</p>
  </div>
  <div style="display:flex;gap:8px">
    <a href="<?= url('/tasks/kanban') ?>" class="btn ghost sm"><svg class="ico" style="width:15px;height:15px"><use href="#i-grid"/></svg>Канбан</a>
    <a href="<?= url('/tasks/calendar') ?>" class="btn ghost sm"><svg class="ico" style="width:15px;height:15px"><use href="#i-cal"/></svg>Календар</a>
    <?php if (\App\Core\Acl::can('tasks.create')): ?>
    <a href="<?= url('/tasks/create') ?>" class="btn sm"><svg class="ico" style="width:15px;height:15px"><use href="#i-plus"/></svg>Нове</a>
    <?php endif; ?>
  </div>
</div>

<?php if (empty($tasks)): ?>
  <div class="empty">Завдань не знайдено. <?php if (\App\Core\Acl::can('tasks.create')): ?><a href="<?= url('/tasks/create') ?>">Створити перше</a><?php endif; ?></div>
<?php else: ?>
<table class="tbl">
  <thead><tr><th>Код</th><th>Назва</th><th>Пріоритет</th><th>Статус</th><th>Строк</th></tr></thead>
  <tbody>
  <?php foreach ($tasks as $t):
    $overdue = $t['due_at'] && strtotime($t['due_at']) < time() && !in_array($t['status'], ['done','cancelled']);
    $statusLabel = match($t['status']) { 'new'=>'Нове','in_progress'=>'В роботі','on_hold'=>'На паузі','review'=>'На перевірці','done'=>'Виконано','cancelled'=>'Скасовано', default=>$t['status'] };
  ?>
  <tr>
    <td><span style="font-family:monospace;font-size:12px;color:var(--mute)"><?= e($t['code'] ?? '') ?></span></td>
    <td><a href="<?= url('/tasks/' . ($t['public_id'] ?? '')) ?>" style="color:var(--text);font-weight:500"><?= e($t['title'] ?? '') ?></a></td>
    <td><span class="badge badge-<?= $t['priority']==='critical'?'bad':($t['priority']==='high'?'warn':'accent') ?>"><?= e($t['priority'] ?? '') ?></span></td>
    <td><span class="badge badge-<?= $overdue?'bad':($t['status']==='done'?'ok':'accent') ?>"><?= e($statusLabel) ?></span></td>
    <td style="font-size:12px;color:<?= $overdue?'var(--bad)':'var(--mute)' ?>"><?= $t['due_at'] ? dt($t['due_at'], 'd.m.Y') : '—' ?></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
<?php \App\Core\View::endSection(); ?>
