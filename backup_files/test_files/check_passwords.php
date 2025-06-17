<?php
// Запускаем сессию
session_start();

// Подключаем файл с функциями для работы с базой данных
require_once 'config/db_connect.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Проверка паролей - CYBERX</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Sofia+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="diagnostic-styles.css">
</head>
<body>
    <div class="diagnostic-container">
        <h1>Проверка хешированных паролей</h1>

        <!-- Генерация хешей для тестовых паролей -->
        <h2>Сгенерированные хеши</h2>
        <?php
        // Генерируем хеши для тестовых паролей
        $admin_password = "admin123";
        $user_password = "user123";

        $admin_hash = password_hash($admin_password, PASSWORD_DEFAULT);
        $user_hash = password_hash($user_password, PASSWORD_DEFAULT);
        ?>
        <pre>
Пароль 'admin123': <?php echo $admin_hash; ?>
Пароль 'user123': <?php echo $user_hash; ?>
        </pre>

        <!-- Проверка паролей в базе данных -->
        <h2>Проверка паролей в базе данных</h2>
        <?php
        try {
            // Получаем пользователей из базы данных
            $stmt = $pdo->query("SELECT id, username, password FROM users");
            $users = $stmt->fetchAll();
            
            echo "<table>";
            echo "<tr><th>ID</th><th>Имя пользователя</th><th>Хеш пароля</th><th>admin123</th><th>user123</th></tr>";
            
            foreach ($users as $user) {
                $check_admin = password_verify($admin_password, $user['password']) ? "<span class='success'>✓</span>" : "<span class='error'>✗</span>";
                $check_user = password_verify($user_password, $user['password']) ? "<span class='success'>✓</span>" : "<span class='error'>✗</span>";
                
                echo "<tr>";
                echo "<td>{$user['id']}</td>";
                echo "<td>{$user['username']}</td>";
                echo "<td><code>{$user['password']}</code></td>";
                echo "<td style='text-align:center;'>{$check_admin}</td>";
                echo "<td style='text-align:center;'>{$check_user}</td>";
                echo "</tr>";
            }
            
            echo "</table>";
            
            // Обновляем пароли, если нужно
            echo "<h2>Обновление паролей</h2>";
            
            if (isset($_GET['update']) && $_GET['update'] == 'true') {
                // Обновляем пароль для admin
                $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE username = 'admin'");
                $stmt->bindParam(':password', $admin_hash, PDO::PARAM_STR);
                $stmt->execute();
                
                // Обновляем пароль для user1
                $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE username = 'user1'");
                $stmt->bindParam(':password', $user_hash, PDO::PARAM_STR);
                $stmt->execute();
                
                echo "<div class='success-box'>";
                echo "<p>Пароли успешно обновлены!</p>";
                echo "<p><a href='check_passwords.php'>Обновить страницу</a> для проверки результатов.</p>";
                echo "</div>";
            } else {
                echo "<p><a href='check_passwords.php?update=true' class='btn'>Обновить пароли</a> для пользователей admin и user1.</p>";
            }
            
        } catch (PDOException $e) {
            echo "<div class='error-box'>";
            echo "<p class='error'>Ошибка при работе с базой данных: " . $e->getMessage() . "</p>";
            echo "</div>";
        }
        ?>

        <!-- Ссылки для тестирования -->
        <div class="links-section">
            <h2>Тестовые ссылки</h2>
            <ul>
                <li><a href="check_database.php" class="btn btn-outline">Проверка базы данных</a></li>
                <li><a href="fix_sessions.php" class="btn btn-outline">Проверка сессий</a></li>
                <li><a href="test_cart.php" class="btn btn-outline">Тестирование корзины</a></li>
                <li><a href="session_fix_instructions.php" class="btn btn-outline">Инструкции по исправлению проблем</a></li>
                <li><a href="login.php" class="btn">Страница входа</a></li>
                <li><a href="index.php" class="btn">Главная страница</a></li>
            </ul>
        </div>
    </div>
</body>
</html> 