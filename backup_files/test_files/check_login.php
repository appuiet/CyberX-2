<?php
require_once 'config/db_connect.php';
require_once 'includes/functions.php';

echo "<h1>Проверка авторизации</h1>";

// Проверяем наличие пользователя admin
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<p>Пользователь admin найден:</p>";
        echo "<ul>";
        echo "<li>ID: " . $admin['id'] . "</li>";
        echo "<li>Username: " . $admin['username'] . "</li>";
        echo "<li>Email: " . $admin['email'] . "</li>";
        echo "<li>Хеш пароля: " . substr($admin['password'], 0, 20) . "...</li>";
        echo "</ul>";
        
        // Проверяем, работает ли функция password_verify
        $test_password = 'admin';
        if (password_verify($test_password, $admin['password'])) {
            echo "<p style='color:green;'>Функция password_verify успешно верифицирует пароль 'admin'</p>";
        } else {
            echo "<p style='color:red;'>Функция password_verify НЕ верифицирует пароль 'admin'</p>";
            
            // Пробуем создать новый хеш и проверить его
            $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
            echo "<p>Новый хеш для 'admin': " . $new_hash . "</p>";
            
            if (password_verify($test_password, $new_hash)) {
                echo "<p style='color:green;'>Функция password_verify работает корректно с новым хешем</p>";
                
                // Обновляем пароль в базе данных
                $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE username = 'admin'");
                $stmt->bindParam(':password', $new_hash);
                if ($stmt->execute()) {
                    echo "<p style='color:green;'>Пароль обновлен!</p>";
                } else {
                    echo "<p style='color:red;'>Ошибка при обновлении пароля</p>";
                }
            } else {
                echo "<p style='color:red;'>Проблема с функцией password_verify</p>";
            }
        }
    } else {
        echo "<p style='color:red;'>Пользователь admin не найден!</p>";
        
        // Создаем пользователя admin
        $username = 'admin';
        $password = 'admin';
        $email = 'admin@cyberx.com';
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, role) 
                              VALUES (:username, :password, :email, 'Администратор', 'admin')");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':email', $email);
        
        if ($stmt->execute()) {
            echo "<p style='color:green;'>Пользователь admin создан!</p>";
            echo "<p>Логин: admin@cyberx.com</p>";
            echo "<p>Пароль: admin</p>";
        } else {
            echo "<p style='color:red;'>Ошибка при создании пользователя</p>";
        }
    }
    
    // Тестируем форму входа
    echo "<h2>Тестовая форма входа</h2>";
    echo "<form method='post' action='login.php'>";
    echo "<div>";
    echo "<label>Email:</label>";
    echo "<input type='text' name='email' value='admin@cyberx.com'>";
    echo "</div>";
    echo "<div style='margin-top: 10px;'>";
    echo "<label>Пароль:</label>";
    echo "<input type='password' name='password' value='admin'>";
    echo "</div>";
    echo "<div style='margin-top: 10px;'>";
    echo "<button type='submit'>Войти</button>";
    echo "</div>";
    echo "</form>";
    
} catch (PDOException $e) {
    echo "<p style='color:red;'>Ошибка: " . $e->getMessage() . "</p>";
}
?> 