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
    <title>Проверка базы данных - CYBERX</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Sofia+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="diagnostic-styles.css">
</head>
<body>
    <div class="diagnostic-container">
        <h1>Проверка структуры базы данных</h1>

        <!-- Проверяем подключение к базе данных -->
        <h2>Подключение к базе данных</h2>
        <?php
        try {
            // Проверяем соединение с базой данных
            $pdo->query("SELECT 1");
            echo "<div class='success-box'>";
            echo "<p class='success'>✓ Подключение к базе данных успешно установлено.</p>";
            echo "<p>Используемый хост: " . $db_host . "</p>";
            echo "<p>Используемый порт: " . $db_port . "</p>";
            echo "<p>Имя базы данных: " . $db_name . "</p>";
            echo "</div>";
        } catch (PDOException $e) {
            echo "<div class='error-box'>";
            echo "<p class='error'>✗ Ошибка подключения к базе данных: " . $e->getMessage() . "</p>";
            echo "</div>";
            exit;
        }
        ?>

        <!-- Проверяем существование таблиц -->
        <h2>Проверка таблиц</h2>
        <?php
        $required_tables = [
            'users', 'categories', 'products', 'orders', 'order_items', 'feedback'
        ];

        try {
            // Получаем список всех таблиц в базе данных
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo "<p>Найдено таблиц: " . count($tables) . "</p>";
            echo "<ul>";
            foreach ($tables as $table) {
                $status = in_array($table, $required_tables) ? "<span class='success'>✓</span>" : "<span class='warning'>?</span>";
                echo "<li>$status $table</li>";
            }
            echo "</ul>";
            
            // Проверяем отсутствующие таблицы
            $missing_tables = array_diff($required_tables, $tables);
            if (!empty($missing_tables)) {
                echo "<div class='warning-box'>";
                echo "<p class='warning'>Отсутствуют следующие таблицы:</p>";
                echo "<ul>";
                foreach ($missing_tables as $table) {
                    echo "<li>$table</li>";
                }
                echo "</ul>";
                echo "</div>";
            }
        } catch (PDOException $e) {
            echo "<div class='error-box'>";
            echo "<p class='error'>Ошибка при получении списка таблиц: " . $e->getMessage() . "</p>";
            echo "</div>";
        }
        ?>

        <!-- Проверяем структуру таблицы users -->
        <h2>Структура таблицы users</h2>
        <?php
        try {
            $stmt = $pdo->query("DESCRIBE users");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table>";
            echo "<tr><th>Поле</th><th>Тип</th><th>Null</th><th>Ключ</th><th>По умолчанию</th><th>Дополнительно</th></tr>";
            
            foreach ($columns as $column) {
                echo "<tr>";
                echo "<td>{$column['Field']}</td>";
                echo "<td>{$column['Type']}</td>";
                echo "<td>{$column['Null']}</td>";
                echo "<td>{$column['Key']}</td>";
                echo "<td>{$column['Default']}</td>";
                echo "<td>{$column['Extra']}</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } catch (PDOException $e) {
            echo "<div class='error-box'>";
            echo "<p class='error'>Ошибка при получении структуры таблицы users: " . $e->getMessage() . "</p>";
            echo "</div>";
        }
        ?>

        <!-- Проверяем количество пользователей -->
        <h2>Пользователи в системе</h2>
        <?php
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM users");
            $user_count = $stmt->fetchColumn();
            
            echo "<p>Всего пользователей: <strong>$user_count</strong></p>";
            
            if ($user_count > 0) {
                $stmt = $pdo->query("SELECT id, username, email, role FROM users LIMIT 10");
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<table>";
                echo "<tr><th>ID</th><th>Имя пользователя</th><th>Email</th><th>Роль</th></tr>";
                
                foreach ($users as $user) {
                    echo "<tr>";
                    echo "<td>{$user['id']}</td>";
                    echo "<td>{$user['username']}</td>";
                    echo "<td>{$user['email']}</td>";
                    echo "<td>{$user['role']}</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
            }
        } catch (PDOException $e) {
            echo "<div class='error-box'>";
            echo "<p class='error'>Ошибка при получении информации о пользователях: " . $e->getMessage() . "</p>";
            echo "</div>";
        }
        ?>

        <!-- Ссылки для тестирования -->
        <div class="links-section">
            <h2>Тестовые ссылки</h2>
            <ul>
                <li><a href="check_passwords.php" class="btn btn-outline">Проверка паролей</a></li>
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