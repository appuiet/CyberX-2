<?php
// Параметры подключения к базе данных
$host = 'localhost';
$port = 3306;
$dbname = 'cyberx_db';
$username = 'root';
$password = 'root';

try {
    $pdo_check = new PDO("mysql:host=$host;port=$port", $username, $password);
    $pdo_check->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Проверяем существование базы данных
    $stmt = $pdo_check->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    if (!$stmt->fetch()) {
        // Если база данных не существует, перенаправляем на страницу установки
        header('Location: install.php');
        exit;
    }
    
    // Создаем PDO подключение к существующей базе данных
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Устанавливаем режим обработки ошибок
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Устанавливаем режим выборки по умолчанию
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // В случае ошибки подключения выводим сообщение
    die("Ошибка подключения к базе данных: " . $e->getMessage() . 
        "<br><br>Пожалуйста, убедитесь, что сервер MySQL запущен в XAMPP и <a href='install.php'>запустите установку базы данных</a>.");
}
?> 