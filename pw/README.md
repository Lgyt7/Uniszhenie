# Почему плохо не ходить на ПиРВП — учебный сайт на PHP

## Запуск
Нужен PHP 8.1+ с расширениями pdo_sqlite и mbstring (в XAMPP/OpenServer есть из коробки).

    cd папка_проекта
    php -S localhost:8000

Откройте http://localhost:8000 (или положите папку в htdocs/www).
База `data/app.sqlite` создаётся сама при первом заходе.

## Администратор (Глеб Тевс)
Почта: gleb.tevs@example.com
Пароль: admin12345
(меняются в includes/config.php; при входе — автоматический переход в admin.php)


## Структура
index.php — тезисы | login.php / register.php / logout.php — авторизация
feedback.php — форма (только для вошедших, иначе редирект на login.php)
admin.php — админ-панель | includes/ — БД, auth, шаблоны | assets/ — css, js, img
