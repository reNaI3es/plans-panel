<?php declare(strict_types=1);
\App\Core\View::startSection('content');
?>
<div class="head">
  <div><h1>ШІ-помічник</h1><p>Запитай що завгодно.</p></div>
</div>
<div style="display:grid;grid-template-columns:240px 1fr;gap:24px;height:calc(100vh - 200px)">
  <div style="border:1px solid var(--line);border-radius:var(--r);padding:12px;overflow-y:auto">
    <h3 style="font:500 13.5px 'Manrope';color:var(--mute);margin:0 0 12px">Діалоги</h3>
    <?php if (empty($conversations)): ?>
      <div class="empty" style="padding:16px 0">Немає діалогів</div>
    <?php else: foreach ($conversations as $c): ?>
    <div style="padding:8px;border-bottom:1px solid var(--line);font-size:13px;cursor:pointer">
      <?= e(str_limit($c['title'] ?? 'Діалог', 30)) ?>
      <div style="font-size:11px;color:var(--mute);margin-top:2px"><?= (int)$c['message_count'] ?> повідомлень</div>
    </div>
    <?php endforeach; endif; ?>
  </div>
  <div style="border:1px solid var(--line);border-radius:var(--r);display:flex;flex-direction:column">
    <div style="flex:1;overflow-y:auto;padding:16px" id="chatMessages">
      <div style="text-align:center;color:var(--mute);padding:40px 0">Почніть новий діалог</div>
    </div>
    <form method="POST" action="<?= url('/ai/chat') ?>" style="border-top:1px solid var(--line);padding:16px">
      <?= csrf_field() ?>
      <div style="display:flex;gap:8px">
        <input type="text" name="message" placeholder="Ваше повідомлення..." required style="flex:1">
        <button type="submit" class="btn sm">Надіслати</button>
      </div>
    </form>
  </div>
</div>
<?php \App\Core\View::endSection(); ?>
