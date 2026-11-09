<?php
require_once __DIR__ . '/db.php';

function e(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function current_user(): ?array
{
    if (empty($_SESSION['uid'])) return null;
    static $u = null;
    if ($u === null) {
        $st = db()->prepare('SELECT id, name, email, role FROM users WHERE id = ?');
        $st->execute([$_SESSION['uid']]);
        $u = $st->fetch() ?: false;
    }
    return $u ?: null;
}

function redirect(string $url): never { header('Location: ' . $url); exit; }

// Только внутренние страницы — защита от open redirect
function safe_next(?string $next): string
{
    $allowed = ['index.php', 'feedback.php', 'admin.php'];
    return in_array($next, $allowed, true) ? $next : 'index.php';
}

function require_login(string $from): array
{
    $u = current_user();
    if (!$u) {
        flash('info', 'Сначала войдите в аккаунт — отправить покаяние могут только авторизованные.');
        redirect('login.php?next=' . urlencode($from));
    }
    return $u;
}

function require_admin(): array
{
    $u = require_login('admin.php');
    if ($u['role'] !== 'admin') { flash('error', 'Админ-панель доступна только администратору.'); redirect('index.php'); }
    return $u;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function csrf_check(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Ошибка безопасности формы. Обновите страницу и попробуйте снова.');
    }
}

function flash(string $type, string $text): void { $_SESSION['flash'] = [$type, $text]; }
function take_flash(): ?array { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }

// Картинка-заглушка: положите файл в assets/img/ с этим именем — и он появится сам
function pic(string $file, string $label, string $class = ''): string
{
    $path = __DIR__ . '/../assets/img/' . $file;
    if (is_file($path)) {
        return '<figure class="pic ' . e($class) . '"><img src="assets/img/' . e($file) . '" alt="' . e($label) . '"></figure>';
    }
    return '<figure class="pic placeholder ' . e($class) . '"><div><strong>Здесь будет картинка</strong>'
         . '<span>' . e($label) . '</span><code>assets/img/' . e($file) . '</code></div></figure>';
}
