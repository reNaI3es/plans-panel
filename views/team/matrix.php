<?php declare(strict_types=1);
\App\Core\View::startSection('content');
?>
<div class="head">
  <div><h1>Матриця відповідальності</h1><p>Хто за що відповідає. Клікніть на ячейку щоб змінити.</p></div>
  <a href="<?= url('/team') ?>" class="btn ghost sm">← Команда</a>
</div>
<div style="overflow-x:auto">
<table class="tbl">
  <thead>
    <tr>
      <th style="min-width:140px">Учасник</th>
      <?php foreach ($categories as $c): ?>
      <th style="text-align:center;min-width:80px"><?= e($c['icon'] ?? '') ?><br><span style="font-size:10px"><?= e(str_limit($c['title'], 10)) ?></span></th>
      <?php endforeach; ?>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($members as $m): ?>
  <tr>
    <td><a href="<?= url('/team/' . ($m['public_id'] ?? '')) ?>" style="color:var(--text);font-weight:500"><?= e($m['display_name'] ?? '') ?></a><br><span style="font-size:11px;color:var(--mute)">Активних: <?= (int)($m['active_tasks'] ?? 0) ?> · Прострочено: <?= (int)($m['overdue_tasks'] ?? 0) ?></span></td>
    <?php foreach ($categories as $c):
      $cell = $matrix[$m['id']][$c['id']] ?? null;
    ?>
    <td style="text-align:center;cursor:pointer" onclick="openAssignModal(<?= (int)$m['id'] ?>, <?= (int)$c['id'] ?>, '<?= e($cell['role_in_category'] ?? 'none') ?>', '<?= e($cell['responsibilities'] ?? '') ?>', <?= (int)($cell['workload'] ?? 2) ?>)">
      <?php if ($cell): ?>
        <span class="badge badge-<?= $cell['role_in_category'] === 'lead' ? 'warn' : 'accent' ?>"><?= e($cell['role_in_category']) ?></span>
      <?php else: ?>
        <span style="color:var(--line-2)">—</span>
      <?php endif; ?>
    </td>
    <?php endforeach; ?>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<!-- Модальное окно -->
<div id="assignModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:1000;align-items:center;justify-content:center">
  <form method="POST" action="<?= url('/team/assign') ?>" style="background:var(--panel);border:1px solid var(--line);border-radius:var(--r);padding:24px;max-width:400px;width:90%">
    <?= csrf_field() ?>
    <input type="hidden" name="user_id" id="modalUserId">
    <input type="hidden" name="category_id" id="modalCategoryId">
    <h3 style="margin-bottom:16px">Призначити відповідальність</h3>
    <div class="f"><label>Роль</label><select name="role_in_category" id="modalRole"><option value="none">Не призначено</option><option value="lead">Відповідальний</option><option value="member">Учасник</option><option value="backup">Запасний</option></select></div>
    <div class="f"><label>Обов'язки</label><textarea name="responsibilities" id="modalResponsibilities" rows="3" placeholder="Опишіть що потрібно робити..."></textarea></div>
    <div class="f"><label>Навантаження (1-5)</label><input type="number" name="workload" id="modalWorkload" min="1" max="5" value="2"></div>
    <div style="display:flex;gap:8px;margin-top:20px"><button type="submit" class="btn">Зберегти</button><button type="button" class="btn ghost" onclick="closeAssignModal()">Скасувати</button></div>
  </form>
</div>
<script>
function openAssignModal(userId, categoryId, role, responsibilities, workload) {
  document.getElementById('modalUserId').value = userId;
  document.getElementById('modalCategoryId').value = categoryId;
  document.getElementById('modalRole').value = role || 'none';
  document.getElementById('modalResponsibilities').value = responsibilities || '';
  document.getElementById('modalWorkload').value = workload || 2;
  document.getElementById('assignModal').style.display = 'flex';
}
function closeAssignModal() {
  document.getElementById('assignModal').style.display = 'none';
}
</script>
<?php \App\Core\View::endSection(); ?>
