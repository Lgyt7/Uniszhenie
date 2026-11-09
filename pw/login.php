<?php
require_once __DIR__ . '/includes/auth.php';

if (current_user()) redirect('index.php');

$next = safe_next($_GET['next'] ?? $_POST['next'] ?? null);
$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = mb_strtolower(trim($_POST['email'] ?? ''));
    $pass = $_POST['password'] ?? '';

    $st = db()->prepare('SELECT * FROM users WHERE email = ?');
    $st->execute([$email]);
    $u = $st->fetch();

    if ($u && password_verify($pass, $u['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['uid'] = (int)$u['id'];
        // Аккаунт администратора (Глеб Тевс) — сразу в админ-панель
        if ($u['role'] === 'admin') redirect('admin.php');
        flash('success', 'Добро пожаловать, ' . $u['name'] . '!');
        redirect($next);
    }
    $error = 'Неверная почта или пароль.';
}

$title = 'Вход';
$bodyClass = 'auth';
require __DIR__ . '/includes/header.php';
?>
<section class="card auth-card">
  <h1>Вход</h1>
  <?php if ($error): ?><div class="flash error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" novalidate data-validate>
    <?= csrf_field() ?>
    <input type="hidden" name="next" value="<?= e($next) ?>">
    <label>Почта
      <input type="email" name="email" value="<?= e($email) ?>" required autocomplete="email">
    </label>
    <label>Пароль
      <input type="password" name="password" required autocomplete="current-password">
    </label>
    <button class="btn" type="submit">Войти</button>
  </form>
  <p class="muted">Нет аккаунта? <a href="register.php?next=<?= urlencode($next) ?>">Зарегистрироваться</a></p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
