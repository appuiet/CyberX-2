<?php
require_once 'config/db_connect.php';

// Создаем новый хеш пароля "admin"
$new_password = 'admin';
$new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

try {
    // Обновляем пароль администратора
    $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE username = 'admin'");
    $stmt->bindParam(':password', $new_password_hash);
    $stmt->execute();
    
    $affected_rows = $stmt->rowCount();
    
    if ($affected_rows > 0) {
        echo "<p style='color:green;'>Пароль администратора успешно сброшен!</p>";
        echo "<p>Логин: <strong>admin@cyberx.com</strong></p>";
        echo "<p>Пароль: <strong>admin</strong></p>";
    } else {
        echo "<p style='color:orange;'>Пользователь 'admin' не найден в базе данных.</p>";
        
        // Если пользователь не найден, создаем его
        echo "<p>Создаем нового администратора...</p>";
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, phone, role) 
                              VALUES ('admin', :password, 'admin@cyberx.com', 'Администратор', '+375291234567', 'admin')");
        $stmt->bindParam(':password', $new_password_hash);
        $stmt->execute();
        
        echo "<p style='color:green;'>Администратор успешно создан!</p>";
        echo "<p>Логин: <strong>admin@cyberx.com</strong></p>";
        echo "<p>Пароль: <strong>admin</strong></p>";
    }
    
    // Проверим, как работает авторизация с этим паролем
    $test_password = 'admin';
    $stmt = $pdo->prepare("SELECT password FROM users WHERE username = 'admin'");
    $stmt->execute();
    $user = $stmt->fetch();
    
    if ($user && password_verify($test_password, $user['password'])) {
        echo "<p style='color:green;'>Тест проверки пароля прошел успешно!</p>";
    } else {
        echo "<p style='color:red;'>Тест проверки пароля НЕ ПРОШЕЛ! Возможно, есть проблема с функцией password_verify().</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red;'>Ошибка при сбросе пароля: " . $e->getMessage() . "</p>";
}

// Добавляем ссылку на страницу входа
echo "<p><a href='login.php' style='display: inline-block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px;'>Перейти на страницу входа</a></p>";
?> 