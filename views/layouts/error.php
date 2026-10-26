<?php
declare(strict_types=1);
/**
 * Error layout — для 404, 403, 500.
 */
?>
<!DOCTYPE html>
<html lang="uk" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Помилка — reNaI3es</title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
    <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem; text-align: center;">
        <div>
            <?= \App\Core\View::yieldSection('content') ?>
            <a href="<?= url('/') ?>" class="btn" style="margin-top: 2rem;">← На головну</a>
        </div>
    </div>
</body>
</html>
