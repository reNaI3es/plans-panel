<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>reNaI3es</title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <style>
        .landing { min-height: 100vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: 24px; }
        .landing-content { max-width: 480px; }
        .landing h1 { font-family: var(--font-display); font-size: clamp(28px, 5vw, 40px); font-weight: 300; letter-spacing: -0.03em; margin-bottom: 12px; }
        .landing h1 .accent { color: var(--accent); }
        .landing p { font-size: 14px; color: var(--mute); margin-bottom: 32px; line-height: 1.7; }
        .landing-features { display: flex; gap: 24px; justify-content: center; margin-top: 48px; flex-wrap: wrap; }
        .landing-feature { font-size: 12px; color: var(--mute); }
    </style>
</head>
<body>
    <div class="landing">
        <div class="landing-content page-enter">
            <div class="cube-scene" style="margin-bottom:32px">
                <div class="cube">
                    <div class="cube-face"></div>
                    <div class="cube-face"></div>
                    <div class="cube-face"></div>
                    <div class="cube-face"></div>
                    <div class="cube-face"></div>
                    <div class="cube-face"></div>
                </div>
            </div>

            <h1>reNaI3<span class="accent">e</span>s</h1>
            <p>Закрита панель керування кіберспортивною командою. Завдання, сервери, база знань, ШІ-помічник.</p>

            <a href="<?= url('/login') ?>" class="btn btn-primary">
                Увійти
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="9 18 15 12 9 6"/></svg>
            </a>

            <div class="landing-features">
                <div class="landing-feature">Argon2id + 2FA</div>
                <div class="landing-feature">Kanban + Календар</div>
                <div class="landing-feature">Моніторинг серверів</div>
                <div class="landing-feature">ШІ-помічник</div>
            </div>
        </div>
    </div>
</body>
</html>
