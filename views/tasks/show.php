<?php declare(strict_types=1);
\App\Core\View::startSection('content');
?>
<div style="margin-bottom:24px"><a href="<?= url('/tasks') ?>" style="color:var(--mute);font-size:13px">← Усі завдання</a></div>
<div class="head">
  <div>
    <h1><?= e($task['title'] ?? '') ?></h1>
    <p><span style="font-family:monospace;font-size:12px"><?= e($task['code'] ?? '') ?></span> · <?= e($task['category_title'] ?? '') ?></p>
  </div>
  <div style="display:flex;gap:8px">
    <span class="badge badge-<?= $task['priority']==='critical'?'bad':($task['priority']==='high'?'warn':'accent') ?>"><?= e($task['priority'] ?? '') ?></span>
    <span class="badge badge-<?= $task['status']==='done'?'ok':'accent' ?>"><?= e($task['status'] ?? '') ?></span>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 280px;gap:40px">
  <div>
    <?php if ($task['description'] ?? ''): ?>
    <div style="margin-bottom:32px;line-height:1.7;color:var(--text)"><?= nl2br(e($task['description'])) ?></div>
    <?php endif; ?>

    <?php if (!empty($checklist)): ?>
    <h3 style="font:500 13.5px 'Manrope';color:var(--mute);margin:0 0 12px">Чеклист</h3>
    <div style="border:1px solid var(--line);border-radius:var(--r);margin-bottom:32px">
      <?php foreach ($checklist as $item): ?>
      <div style="padding:10px 16px;border-bottom:1px solid var(--line);display:flex;align-items:center;gap:10px">
        <span style="width:16px;height:16px;border:1px solid var(--line-2);border-radius:3px;display:grid;place-items:center;<?= $item['is_done'] ? 'background:var(--ok);border-color:var(--ok)' : '' ?>">
          <?php if ($item['is_done']): ?><svg style="width:10px;height:10px;stroke:#fff;stroke-width:2;fill:none"><polyline points="1,5 4,8 9,2"/></svg><?php endif; ?>
        </span>
        <span style="<?= $item['is_done'] ? 'color:var(--mute);text-decoration:line-through' : '' ?>"><?= e($item['title'] ?? '') ?></span>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <h3 style="font:500 13.5px 'Manrope';color:var(--mute);margin:0 0 12px">Коментарі</h3>
    <?php foreach ($comments as $c): ?>
    <div style="padding:12px 0;border-top:1px solid var(--line)">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
        <span class="av" style="width:24px;height:24px;font-size:11px"><?= e($c['avatar_initials'] ?? '?') ?></span>
        <strong style="font-size:13px"><?= e($c['display_name'] ?? '') ?></strong>
        <span style="font-size:11px;color:var(--mute)"><?= time_ago($c['created_at'] ?? null) ?></span>
      </div>
      <div style="font-size:13px;color:var(--text);line-height:1.6"><?= nl2br(e($c['body'] ?? '')) ?></div>
    </div>
    <?php endforeach; ?>
    <form method="POST" action="<?= url('/tasks/' . ($task['public_id'] ?? '') . '/comment') ?>" style="margin-top:16px">
      <?= csrf_field() ?>
      <textarea name="body" rows="3" placeholder="Додати коментар..." required></textarea>
      <button type="submit" class="btn sm" style="margin-top:8px">Надіслати</button>
    </form>
  </div>

  <div>
    <div style="border:1px solid var(--line);border-radius:var(--r);padding:16px">
      <h3 style="font:500 13.5px 'Manrope';color:var(--mute);margin:0 0 12px">Деталі</h3>
      <div style="font-size:13px">
        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--line)"><span style="color:var(--mute)">Тип</span><span><?= e($task['type'] ?? '') ?></span></div>
        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--line)"><span style="color:var(--mute)">Строк</span><span><?= dt($task['due_at'] ?? null, 'd.m.Y H:i') ?></span></div>
        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--line)"><span style="color:var(--mute)">Створив</span><span><?= e($task['creator_name'] ?? '') ?></span></div>
        <div style="display:flex;justify-content:space-between;padding:6px 0"><span style="color:var(--mute)">Створено</span><span><?= dt($task['created_at'] ?? null, 'd.m.Y') ?></span></div>
      </div>
    </div>
    <form method="POST" action="<?= url('/tasks/' . ($task['public_id'] ?? '') . '/status') ?>" style="margin-top:16px">
      <?= csrf_field() ?>
      <select name="status" style="margin-bottom:8px">
        <?php foreach (['new'=>'Нове','in_progress'=>'В роботі','on_hold'=>'На паузі','review'=>'На перевірці','done'=>'Виконано','cancelled'=>'Скасовано'] as $v=>$l): ?>
        <option value="<?= $v ?>" <?= ($task['status'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn ghost sm full">Оновити статус</button>
    </form>
  </div>
</div>
<?php \App\Core\View::endSection(); ?>
