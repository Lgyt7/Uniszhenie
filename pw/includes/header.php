<?php
require_once __DIR__ . '/auth.php';
$user = current_user();
$flash = take_flash();
$page = $page ?? '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Пара без тебя') ?> — ПиРВП</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700&family=Unbounded:wght@500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="<?= e($bodyClass ?? '') ?>">
<header class="top">
  <a class="brand" href="index.php"><span class="mark">Н</span>ПиРВП</a>
  <nav>
    <a href="index.php" <?= $page === 'home' ? 'aria-current="page"' : '' ?>>Тезисы</a>
    <a href="feedback.php" <?= $page === 'feedback' ? 'aria-current="page"' : '' ?>>Покаяться</a>
    <?php if ($user && $user['role'] === 'admin'): ?>
      <a href="admin.php" <?= $page === 'admin' ? 'aria-current="page"' : '' ?>>Админ-панель</a>
    <?php endif; ?>
  </nav>
  <div class="who">
    <?php if ($user): ?>
      <span class="uname"><?= e($user['name']) ?></span>
      <a class="btn small ghost" href="logout.php">Выйти</a>
    <?php else: ?>
      <a class="btn small ghost" href="login.php">Войти</a>
      <a class="btn small" href="register.php">Регистрация</a>
    <?php endif; ?>
  </div>
</header>
<main>
<?php if ($flash): ?>
  <div class="flash <?= e($flash[0]) ?>" role="status"><?= e($flash[1]) ?></div>
<?php endif; ?>
