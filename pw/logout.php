<?php
require_once __DIR__ . '/includes/auth.php';
$_SESSION = [];
session_destroy();
session_start();
flash('info', 'Вы вышли из аккаунта.');
redirect('index.php');
