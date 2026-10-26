<?php declare(strict_types=1);
\App\Core\View::startSection('content');
$monthNames = ['','Січень','Лютий','Березень','Квітень','Травень','Червень','Липень','Серпень','Вересень','Жовтень','Листопад','Грудень'];
$firstDow = (int)date('N', mktime(0,0,0,$month,1,$year));
$daysInMonth = (int)date('t', mktime(0,0,0,$month,1,$year));
$prevM = $month-1; $prevY = $year; if($prevM<1){$prevM=12;$prevY--;}
$nextM = $month+1; $nextY = $year; if($nextM>12){$nextM=1;$nextY++;}
?>
<div class="head">
  <div><h1>Календар</h1></div>
  <div style="display:flex;gap:8px;align-items:center">
    <a href="<?= url('/tasks') ?>" class="btn ghost sm">Список</a>
    <a href="<?= url('/tasks/kanban') ?>" class="btn ghost sm">Канбан</a>
  </div>
</div>
<div style="border:1px solid var(--line);border-radius:var(--r);padding:20px">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
    <a href="<?= url('/tasks/calendar', ['year'=>$prevY,'month'=>$prevM]) ?>" class="btn ghost sm">←</a>
    <h2 style="font:200 24px 'Unbounded';margin:0"><?= e($monthNames[$month]) ?> <?= $year ?></h2>
    <a href="<?= url('/tasks/calendar', ['year'=>$nextY,'month'=>$nextM]) ?>" class="btn ghost sm">→</a>
  </div>
  <div class="cal-grid">
    <?php foreach(['Пн','Вт','Ср','Чт','Пт','Сб','Нд'] as $d): ?>
    <div class="cal-header"><?= $d ?></div>
    <?php endforeach; ?>
    <?php for($i=1;$i<$firstDow;$i++): ?><div class="cal-day"></div><?php endfor; ?>
    <?php for($day=1;$day<=$daysInMonth;$day++):
      $isToday = $day==(int)date('j') && $month==(int)date('m') && $year==(int)date('Y');
      $tasks = $byDay[$day] ?? [];
    ?>
    <div class="cal-day <?= $isToday ? 'today' : '' ?>">
      <div class="cal-day-num"><?= $day ?></div>
      <?php foreach(array_slice($tasks,0,3) as $t): ?>
      <a href="<?= url('/tasks/' . ($t['public_id'] ?? '')) ?>" class="cal-task"><?= e($t['code'] ?? '') ?></a>
      <?php endforeach; ?>
    </div>
    <?php endfor; ?>
  </div>
</div>
<?php \App\Core\View::endSection(); ?>
