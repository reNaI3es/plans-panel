<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="uk">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Вхід · reNaI3es</title>
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
<?php include __DIR__ . '/../partials/icons.php'; ?>
</head>
<body>
<main class="login">
  <div class="word" aria-hidden="true">reNaI3es</div>
  <div class="floor" aria-hidden="true"></div>
  <div class="sh abs" data-d="-26" style="--z:96px;left:12%;top:30%" aria-hidden="true"><div class="cube3"><b></b><b></b><b></b><b></b><b></b><b></b></div></div>
  <div class="sh abs" data-d="34" style="--z:140px;right:12%;top:24%" aria-hidden="true"><div class="ring"><i></i><i></i><i></i><i></i></div></div>
  <div class="sh abs hide-m" data-d="-44" style="--z:110px;left:20%;bottom:20%" aria-hidden="true"><div class="plates"><i></i><i></i><i></i></div></div>
  <form class="box" method="POST" action="<?= url('/login') ?>">
    <?= csrf_field() ?>
    <h1>Вхід</h1>
    <p>Панель планів для команди.</p>
    <?php $error = flash('error'); if ($error): ?>
      <div class="flash flash-error"><?= e($error) ?></div>
    <?php endif; ?>
    <div class="f"><label for="u">Логін</label><input id="u" name="login" type="text" value="<?= e(old('login')) ?>" autocomplete="username" required></div>
    <div class="f"><label for="p">Пароль</label><input id="p" name="password" type="password" autocomplete="current-password" required></div>
    <button class="btn full" type="submit">Увійти</button>
    <p class="note"><a href="<?= url('/password-reset') ?>">Забули пароль?</a></p>
  </form>
</main>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
