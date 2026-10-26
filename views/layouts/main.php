<?php declare(strict_types=1);
$user = \App\Core\Auth::user();
$isImpersonating = \App\Core\Auth::isImpersonating();
$pageTitle = $pageTitle ?? 'reNaI3es';
$uri = $_SERVER['REQUEST_URI'] ?? '';
$on = fn(string $path) => str_contains($uri, $path) ? 'on' : '';
?>
<!DOCTYPE html>
<html lang="uk">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<title><?= e($pageTitle) ?> · reNaI3es</title>
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
<?php include __DIR__ . '/../partials/icons.php'; ?>
</head>
<body>
<?php if ($isImpersonating): ?>
<div class="imp-banner">Ви переглядаєте від імені іншого користувача
<form method="POST" action="<?= url('/stop-impersonate') ?>" style="display:inline;margin-left:12px">
<?= csrf_field() ?><button type="submit" style="background:none;border:0;color:#fff;cursor:pointer;font-weight:600">Вийти</button>
</form></div>
<?php endif; ?>

<div class="app">
  <nav class="rail" aria-label="Розділи">
    <div class="mark">r</div>
    <a href="<?= url('/dashboard') ?>" class="<?= $on('/dashboard') ?>" data-t="Головна"><svg class="ico"><use href="#i-home"/></svg></a>
    <a href="<?= url('/tasks') ?>" class="<?= $on('/tasks') ?>" data-t="Завдання"><svg class="ico"><use href="#i-tasks"/></svg></a>
    <a href="<?= url('/team') ?>" class="<?= $on('/team') ?>" data-t="Команда"><svg class="ico"><use href="#i-team"/></svg></a>
    <a href="<?= url('/kb') ?>" class="<?= $on('/kb') ?>" data-t="База знань"><svg class="ico"><use href="#i-book"/></svg></a>
    <a href="<?= url('/servers') ?>" class="<?= $on('/servers') ?>" data-t="Сервери"><svg class="ico"><use href="#i-srv"/></svg></a>
    <a href="<?= url('/ai') ?>" class="<?= $on('/ai') ?>" data-t="ШІ-помічник"><svg class="ico"><use href="#i-ai"/></svg></a>
    <a href="<?= url('/notifications') ?>" class="<?= $on('/notifications') ?>" data-t="Сповіщення"><svg class="ico"><use href="#i-bell"/></svg></a>
    <?php if ($user && $user['role_slug'] === 'owner'): ?>
    <a href="<?= url('/admin/settings') ?>" class="<?= $on('/admin') ?>" data-t="Налаштування"><svg class="ico"><use href="#i-cog"/></svg></a>
    <?php endif; ?>
    <div class="sp"></div>
  </nav>

  <div class="work">
    <header class="bar">
      <a class="brand" href="<?= url('/dashboard') ?>">
        <div class="sh" style="--z:20px" aria-hidden="true"><div class="cube3" style="--d:14s"><b></b><b></b><b></b><b></b><b></b><b></b></div></div>
        renai3es
      </a>
      <div class="search">
        <svg class="ico"><use href="#i-find"/></svg>
        <input id="globalSearch" type="text" placeholder="Знайти завдання" aria-label="Пошук">
        <kbd>/</kbd>
      </div>
      <div class="who">
        <b><?= e($user['display_name'] ?? '') ?></b>
        <a href="<?= url('/logout') ?>">Вийти</a>
      </div>
    </header>

    <main class="pg">
      <?php
      $flashSuccess = flash('success');
      $flashError = flash('error');
      if ($flashSuccess) echo '<div class="flash flash-success">' . e($flashSuccess) . '</div>';
      if ($flashError) echo '<div class="flash flash-error">' . e($flashError) . '</div>';
      ?>
      <?= \App\Core\View::yieldSection('content') ?>
    </main>
  </div>
</div>

<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
