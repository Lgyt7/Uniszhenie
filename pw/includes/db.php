<?php
// Подключение к SQLite + автосоздание таблиц и аккаунта админа
require_once __DIR__ . '/config.php';
session_start();

const DB_PATH = __DIR__ . '/../data/app.sqlite';

function db(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;

    if (!is_dir(dirname(DB_PATH))) mkdir(dirname(DB_PATH), 0775, true);
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');

    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'user',
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS feedback (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        reason TEXT NOT NULL,
        remorse INTEGER NOT NULL,
        message TEXT NOT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    $exists = $pdo->prepare('SELECT 1 FROM users WHERE email = ?');
    $exists->execute([ADMIN_EMAIL]);
    if (!$exists->fetch()) {
        $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?,?,?,?)')
            ->execute(['Глеб Тевс', ADMIN_EMAIL, password_hash(ADMIN_PASSWORD, PASSWORD_DEFAULT), 'admin']);
    }
    return $pdo;
}
