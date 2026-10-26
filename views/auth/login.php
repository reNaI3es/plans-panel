<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вхід — reNaI3es</title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
    <div class="auth-wrap">
        <div class="auth-card page-enter">
            <!-- 3D куб -->
            <div class="cube-scene">
                <div class="cube">
                    <div class="cube-face"></div>
                    <div class="cube-face"></div>
                    <div class="cube-face"></div>
                    <div class="cube-face"></div>
                    <div class="cube-face"></div>
                    <div class="cube-face"></div>
                </div>
            </div>

            <div class="auth-logo">
                <div class="auth-logo-text">reNaI3es</div>
                <div class="auth-logo-sub">Внутрішня панель керування</div>
            </div>

            <div class="auth-panel">
                <?php $error = flash('error'); if ($error): ?>
                    <div class="flash flash-error"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="<?= url('/login') ?>">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label for="login">Логін</label>
                        <input type="text" id="login" name="login" required autocomplete="username" autofocus value="<?= e(old('login')) ?>">
                    </div>

                    <div class="form-group">
                        <label for="password">Пароль</label>
                        <input type="password" id="password" name="password" required autocomplete="current-password">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;margin-top:8px">Увійти</button>
                </form>
            </div>

            <div class="auth-footer">
                <a href="<?= url('/password-reset') ?>">Забули пароль?</a>
            </div>
        </div>
    </div>
</body>
</html>
