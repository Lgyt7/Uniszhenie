<?php
require_once __DIR__ . '/includes/auth.php';
$admin = require_admin();

// Удаление покаяния (POST + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    csrf_check();
    db()->prepare('DELETE FROM feedback WHERE id = ?')->execute([(int)$_POST['delete_id']]);
    flash('success', 'Запись удалена.');
    redirect('admin.php');
}

$stats = [
    'Студентов'        => (int)db()->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn(),
    'Покаяний'         => (int)db()->query('SELECT COUNT(*) FROM feedback')->fetchColumn(),
    'Среднее раскаяние' => round((float)db()->query('SELECT COALESCE(AVG(remorse),0) FROM feedback')->fetchColumn(), 1),
];

$rows = db()->query('SELECT f.*, u.name, u.email FROM feedback f JOIN users u ON u.id = f.user_id ORDER BY f.id DESC')->fetchAll();
$users = db()->query('SELECT id, name, email, role, created_at FROM users ORDER BY id DESC')->fetchAll();

$title = 'Админ-панель';
$page = 'admin';
$bodyClass = 'admin';
require __DIR__ . '/includes/header.php';
?>
<section class="admin-head">
  <h1>Админ-панель</h1>
  <p class="muted">Здравствуйте, <?= e($admin['name']) ?>. Здесь все покаяния студентов.</p>
</section>

<section class="stats">
  <?php foreach ($stats as $label => $val): ?>
    <div class="stat"><b><?= e((string)$val) ?></b><span><?= e($label) ?></span></div>
  <?php endforeach; ?>
</section>

<section class="card">
  <h2>Покаяния</h2>
  <?php if (!$rows): ?>
    <p class="muted">Пока никто не покаялся. Все ходят на пары?</p>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Дата</th><th>Студент</th><th>Причина</th><th>Раскаяние</th><th>Сообщение</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= e($r['created_at']) ?></td>
          <td><?= e($r['name']) ?><br><span class="muted"><?= e($r['email']) ?></span></td>
          <td><?= e(REASONS[$r['reason']] ?? $r['reason']) ?></td>
          <td><?= (int)$r['remorse'] ?>/10</td>
          <td class="msg"><?= nl2br(e($r['message'])) ?></td>
          <td>
            <form method="post" onsubmit="return confirm('Удалить запись?')">
              <?= csrf_field() ?>
              <input type="hidden" name="delete_id" value="<?= (int)$r['id'] ?>">
              <button class="btn small danger" type="submit">Удалить</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</section>

<section class="card">
  <h2>Пользователи</h2>
  <div class="table-wrap">
    <table>
      <thead><tr><th>ID</th><th>Имя</th><th>Почта</th><th>Роль</th><th>Создан</th></tr></thead>
      <tbody>
      <?php foreach ($users as $u): ?>
        <tr><td><?= (int)$u['id'] ?></td><td><?= e($u['name']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['role']) ?></td><td><?= e($u['created_at']) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
