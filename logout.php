<?php
// Запускаем сессию
session_start();

// Очищаем все данные сессии
$_SESSION = array();

// Уничтожаем куки сессии
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Уничтожаем сессию
session_destroy();

// Перенаправляем на главную страницу
header('Location: index.php');
exit;
?> 