<?php
require_once __DIR__ . '/includes/auth.php';

if (current_user()) redirect('index.php');

$next = safe_next($_GET['next'] ?? $_POST['next'] ?? null);
$errors = [];
$name = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['name'] ?? '');
    $email = mb_strtolower(trim($_POST['email'] ?? ''));
    $pass = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if (mb_strlen($name) < 2 || mb_strlen($name) > 60) $errors[] = 'Имя должно быть от 2 до 60 символов.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Введите корректную почту.';
    if (mb_strlen($pass) < 6) $errors[] = 'Пароль — минимум 6 символов.';
    if ($pass !== $pass2) $errors[] = 'Пароли не совпадают.';

    if (!$errors) {
        $st = db()->prepare('SELECT 1 FROM users WHERE email = ?');
        $st->execute([$email]);
        if ($st->fetch()) {
            $errors[] = 'Аккаунт с такой почтой уже есть.';
        } else {
            db()->prepare('INSERT INTO users (name, email, password_hash) VALUES (?,?,?)')
                ->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT)]);
            session_regenerate_id(true);
            $_SESSION['uid'] = (int)db()->lastInsertId();
            flash('success', 'Аккаунт создан. Теперь можно каяться.');
            redirect($next);
        }
    }
}

$title = 'Регистрация';
$bodyClass = 'auth';
require __DIR__ . '/includes/header.php';
?>
<section class="card auth-card">
  <h1>Регистрация</h1>
  <?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>
  <form method="post" novalidate data-validate>
    <?= csrf_field() ?>
    <input type="hidden" name="next" value="<?= e($next) ?>">
    <label>Имя и фамилия
      <input type="text" name="name" value="<?= e($name) ?>" required minlength="2" maxlength="60" autocomplete="name">
    </label>
    <label>Почта
      <input type="email" name="email" value="<?= e($email) ?>" required autocomplete="email">
    </label>
    <label>Пароль
      <input type="password" name="password" required minlength="6" autocomplete="new-password">
    </label>
    <label>Повторите пароль
      <input type="password" name="password2" required minlength="6" autocomplete="new-password">
    </label>
    <button class="btn" type="submit">Создать аккаунт</button>
  </form>
  <p class="muted">Уже есть аккаунт? <a href="login.php?next=<?= urlencode($next) ?>">Войти</a></p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
