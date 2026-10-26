<?php
declare(strict_types=1);
/**
 * Головний layout — обгортка для всіх сторінок.
 */
$user = \App\Core\Auth::user();
$isImpersonating = \App\Core\Auth::isImpersonating();
$pageTitle = $pageTitle ?? 'reNaI3es';
?>
<!DOCTYPE html>
<html lang="uk" data-theme="<?= e($user['theme'] ?? 'dark') ?>">
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
            ⚠️ Ви перебуваєте в режимі перегляду від імені іншого користувача.
            <form method="POST" action="<?= url('/stop-impersonate') ?>" style="display:inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-danger" style="margin-left:1rem">Вийти</button>
            </form>
        </div>
    <?php endif; ?>

    <div class="app-layout">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="main-content">
            <?php
            // Flash-повідомлення
            $flashSuccess = flash('success');
            $flashError = flash('error');
            if ($flashSuccess) echo '<div class="flash flash-success">' . e($flashSuccess) . '</div>';
            if ($flashError) echo '<div class="flash flash-error">' . e($flashError) . '</div>';
            ?>

            <?= \App\Core\View::yieldSection('content') ?>
        </main>
    </div>

    <script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>
