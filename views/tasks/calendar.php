<?php
declare(strict_types=1);
\App\Core\View::startSection('content');

$monthNames = ['', 'Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень', 'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень'];
$firstDow = (int)date('N', mktime(0, 0, 0, $month, 1, $year));
$daysInMonth = (int)date('t', mktime(0, 0, 0, $month, 1, $year));
$prevMonth = $month - 1; $prevYear = $year;
if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
$nextMonth = $month + 1; $nextYear = $year;
if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h1 style="font-size: 1.5rem; font-weight: 700;">📅 Календар</h1>
    <div style="display: flex; gap: 0.5rem;">
        <a href="<?= url('/tasks') ?>" class="btn btn-sm">📝 Список</a>
        <a href="<?= url('/tasks/kanban') ?>" class="btn btn-sm">📋 Канбан</a>
    </div>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <a href="<?= url('/tasks/calendar', ['year' => $prevYear, 'month' => $prevMonth]) ?>" class="btn btn-sm">←</a>
        <h2 style="font-size: 1.125rem; font-weight: 600;"><?= e($monthNames[$month]) ?> <?= $year ?></h2>
        <a href="<?= url('/tasks/calendar', ['year' => $nextYear, 'month' => $nextMonth]) ?>" class="btn btn-sm">→</a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 1px; background: var(--line);">
        <?php
        $dowNames = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Нд'];
        foreach ($dowNames as $d): ?>
            <div style="background: var(--bg-card); padding: 0.5rem; text-align: center; font-size: 0.75rem; font-weight: 600; color: var(--text-dim);">
                <?= e($d) ?>
            </div>
        <?php endforeach; ?>

        <?php
        // Порожні клітинки перед першим днем
        for ($i = 1; $i < $firstDow; $i++): ?>
            <div style="background: var(--bg); min-height: 80px;"></div>
        <?php endfor; ?>

        <?php
        // Дні місяця
        for ($day = 1; $day <= $daysInMonth; $day++):
            $isToday = $day === (int)date('j') && $month === (int)date('m') && $year === (int)date('Y');
            $tasks = $byDay[$day] ?? [];
        ?>
            <div style="background: var(--bg-card); min-height: 80px; padding: 0.375rem; <?= $isToday ? 'border: 2px solid var(--accent);' : '' ?>">
                <div style="font-size: 0.75rem; font-weight: <?= $isToday ? '700' : '500' ?>; color: <?= $isToday ? 'var(--accent)' : 'var(--text)' ?>; margin-bottom: 0.25rem;">
                    <?= $day ?>
                </div>
                <?php foreach (array_slice($tasks, 0, 3) as $t): ?>
                    <a href="<?= url('/tasks/' . ($t['public_id'] ?? '')) ?>" style="display: block; font-size: 0.6875rem; padding: 0.125rem 0.25rem; background: var(--bg); border-radius: 3px; margin-bottom: 0.125rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--text-dim);">
                        <?= e($t['code'] ?? '') ?>
                    </a>
                <?php endforeach; ?>
                <?php if (count($tasks) > 3): ?>
                    <div style="font-size: 0.625rem; color: var(--text-muted);">+<?= count($tasks) - 3 ?></div>
                <?php endif; ?>
            </div>
        <?php endfor; ?>
    </div>
</div>

<?php
\App\Core\View::endSection();
