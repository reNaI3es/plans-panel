<?php
declare(strict_types=1);
/**
 * Сторінка входу.
 */
?>
<!DOCTYPE html>
<html lang="uk" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вхід — reNaI3es</title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
    <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem;">
        <div style="max-width: 400px; width: 100%;">
            <div style="text-align: center; margin-bottom: 2rem;">
                <h1 style="font-size: 1.5rem; font-weight: 700;">🎮 reNaI3es</h1>
                <p style="color: var(--text-dim); font-size: 0.875rem;">Внутрішня панель керування</p>
            </div>

            <div class="card">
                <?php
                $error = flash('error');
                if ($error): ?>
                    <div class="flash flash-error"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="<?= url('/login') ?>">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label for="login">Логін</label>
                        <input type="text" id="login" name="login" required autocomplete="username" autofocus
                               value="<?= e(old('login')) ?>">
                    </div>

                    <div class="form-group">
                        <label for="password">Пароль</label>
                        <input type="password" id="password" name="password" required autocomplete="current-password">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Увійти</button>
                </form>

                <div style="margin-top: 1rem; text-align: center;">
                    <a href="<?= url('/password-reset') ?>" style="font-size: 0.8125rem; color: var(--text-dim);">
                        Забули пароль?
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
