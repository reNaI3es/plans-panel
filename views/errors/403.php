<?php
declare(strict_types=1);
\App\Core\View::startSection('content');
?>
<div style="font-size: 4rem; margin-bottom: 1rem;">🚫</div>
<h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">403</h1>
<p style="color: var(--text-dim); font-size: 1.125rem;">
    <?= e($message ?? 'Доступ заборонено') ?>
</p>
<?php if (!empty($permission)): ?>
    <p style="color: var(--text-muted); font-size: 0.875rem; margin-top: 0.5rem;">
        Потрібне право: <code><?= e($permission) ?></code>
    </p>
<?php endif; ?>
<?php
\App\Core\View::endSection();
