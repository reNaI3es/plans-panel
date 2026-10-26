<?php
declare(strict_types=1);
/**
 * Лендінг — публічна сторінка до входу.
 */
?>
<!DOCTYPE html>
<html lang="uk" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>reNaI3es — Внутрішня панель</title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <style>
        .hero { min-height: 100vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: 2rem; position: relative; overflow: hidden; }
        .hero-canvas { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 0; }
        .hero-content { position: relative; z-index: 1; max-width: 600px; }
        .hero h1 { font-size: clamp(2rem, 5vw, 3.5rem); font-weight: 800; margin-bottom: 1rem; line-height: 1.1; }
        .hero p { font-size: 1.125rem; color: var(--text-dim); margin-bottom: 2rem; }
    </style>
</head>
<body>
    <div class="hero">
        <canvas id="hero-canvas" class="hero-canvas"></canvas>
        <div class="hero-content">
            <h1>🎮 reNaI3es</h1>
            <p>Закрита панель керування командою.<br>Завдання, сервери, база знань, ШІ-помічник.</p>
            <a href="<?= url('/login') ?>" class="btn btn-primary" style="font-size: 1rem; padding: 0.875rem 2rem;">
                Увійти →
            </a>
        </div>
    </div>
    <script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>
