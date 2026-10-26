<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="uk" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Двофакторна автентифікація — reNaI3es</title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
    <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem;">
        <div style="max-width: 400px; width: 100%;">
            <div style="text-align: center; margin-bottom: 2rem;">
                <h1 style="font-size: 1.5rem; font-weight: 700;">🔐 2FA</h1>
                <p style="color: var(--text-dim); font-size: 0.875rem;">Введіть код з вашого додатку автентифікації</p>
            </div>

            <div class="card">
                <?php $error = flash('error'); if ($error): ?>
                    <div class="flash flash-error"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="<?= url('/2fa') ?>">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label for="code">6-значний код</label>
                        <input type="text" id="code" name="code" required autocomplete="one-time-code"
                               pattern="[0-9]{6}" maxlength="6" inputmode="numeric" autofocus
                               style="text-align: center; font-size: 1.5rem; letter-spacing: 0.5em; font-family: var(--font-mono);">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Підтвердити</button>
                </form>

                <div style="margin-top: 1rem; text-align: center; font-size: 0.8125rem; color: var(--text-dim);">
                    Втрачено доступ? Використайте резервний код замість TOTP.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
