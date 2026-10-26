<?php
declare(strict_types=1);
/**
 * Головний layout.
 */
$user = \App\Core\Auth::user();
$isImpersonating = \App\Core\Auth::isImpersonating();
$pageTitle = $pageTitle ?? 'reNaI3es';
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($pageTitle) ?> — reNaI3es</title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
    <?php if ($isImpersonating): ?>
        <div class="impersonate-banner">
            Ви переглядаєте від імені іншого користувача
            <form method="POST" action="<?= url('/stop-impersonate') ?>" style="display:inline; margin-left:12px">
                <?= csrf_field() ?>
                <button type="submit" class="sidebar-logout" style="color:#fff">Вийти</button>
            </form>
        </div>
    <?php endif; ?>

    <button class="mobile-toggle" id="mobileToggle" aria-label="Меню">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="app-layout">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="main-content page-enter">
            <?php
            $flashSuccess = flash('success');
            $flashError = flash('error');
            if ($flashSuccess) echo '<div class="flash flash-success">' . e($flashSuccess) . '</div>';
            if ($flashError) echo '<div class="flash flash-error">' . e($flashError) . '</div>';
            ?>

            <?= \App\Core\View::yieldSection('content') ?>
        </main>
    </div>

    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
