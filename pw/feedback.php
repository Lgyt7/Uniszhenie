<?php
require_once __DIR__ . '/includes/auth.php';

// Не авторизован — перекидываем на страницу входа
$user = require_login('feedback.php');

$errors = [];
$reason = 'slept';
$remorse = 7;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $reason = $_POST['reason'] ?? '';
    $remorse = (int)($_POST['remorse'] ?? 0);
    $message = trim($_POST['message'] ?? '');

    if (!isset(REASONS[$reason])) $errors[] = 'Выберите причину из списка.';
    if ($remorse < 1 || $remorse > 10) $errors[] = 'Степень раскаяния — от 1 до 10.';
    if (mb_strlen($message) < 10) $errors[] = 'Напишите хотя бы 10 символов — так не каются.';
    if (mb_strlen($message) > 1000) $errors[] = 'Покаяние слишком длинное (максимум 1000 символов).';

    if (!$errors) {
        db()->prepare('INSERT INTO feedback (user_id, reason, remorse, message) VALUES (?,?,?,?)')
            ->execute([$user['id'], $reason, $remorse, $message]);
        flash('success', 'Покаяние принято. Преподаватель его обязательно прочитает.');
        redirect('feedback.php');
    }
}

$mine = db()->prepare('SELECT * FROM feedback WHERE user_id = ? ORDER BY id DESC LIMIT 5');
$mine->execute([$user['id']]);
$mine = $mine->fetchAll();

$title = 'Покаяние';
$page = 'feedback';
require __DIR__ . '/includes/header.php';
?>
<section class="feedback-wrap">
  <div class="card">
    <h1>Извините, что я не пришёл. Я каюсь.</h1>
    <p class="muted">Вы вошли как <?= e($user['name']) ?>. Расскажите, что случилось.</p>
    <?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>
    <form method="post" novalidate data-validate>
      <?= csrf_field() ?>
      <label>Причина пропуска
        <select name="reason" required>
          <?php foreach (REASONS as $key => $label): ?>
            <option value="<?= e($key) ?>" <?= $key === $reason ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Степень раскаяния: <output id="remorse-out"><?= $remorse ?></output> из 10
        <input type="range" name="remorse" min="1" max="10" value="<?= $remorse ?>" id="remorse">
      </label>
      <label>Ваше покаяние
        <textarea name="message" rows="6" maxlength="1000" required placeholder="Больше так не буду, честное слово…"><?= e($message) ?></textarea>
        <small class="counter"><span id="msg-count"><?= mb_strlen($message) ?></span> / 1000</small>
      </label>
      <button class="btn" type="submit">Отправить покаяние</button>
    </form>
  </div>

  <aside class="side">
    <?= pic('feedback-side.jpg', 'Мотивационная картинка рядом с формой', 'tall') ?>
    <?php if ($mine): ?>
      <div class="card mini">
        <h3>Ваши последние покаяния</h3>
        <?php foreach ($mine as $m): ?>
          <p><b><?= e(REASONS[$m['reason']] ?? $m['reason']) ?></b>, раскаяние <?= (int)$m['remorse'] ?>/10<br>
          <span class="muted"><?= e($m['created_at']) ?></span></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </aside>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
