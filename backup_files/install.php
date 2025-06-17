<?php
$page_title = 'Установка базы данных';
require_once 'config/db_connect.php';

// Функция для логирования
function log_message($message, $type = 'info') {
    $color = 'black';
    switch ($type) {
        case 'success': $color = 'green'; break;
        case 'error': $color = 'red'; break;
        case 'warning': $color = 'orange'; break;
    }
    echo "<p style='color:$color;'>$message</p>\n";
}

// Получаем содержимое SQL-файла
$sql_file = file_get_contents('config/database.sql');
if (!$sql_file) {
    log_message("Ошибка: Файл config/database.sql не найден или не может быть прочитан.", 'error');
    exit;
}

// Разделяем файл на отдельные запросы
$queries = explode(';', $sql_file);

// Выполняем каждый запрос
log_message("Начинаем выполнение SQL-запросов:", 'info');
echo "<div style='background-color: #f5f5f5; padding: 10px; max-height: 400px; overflow-y: auto; margin-bottom: 20px;'>";

try {
    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query)) continue;
        
        // Отображаем запрос
        echo "<pre>" . htmlspecialchars($query) . ";</pre>";
        
        // Выполняем запрос
        try {
            $pdo->exec($query);
            log_message("Запрос выполнен успешно.", 'success');
        } catch (PDOException $e) {
            log_message("Ошибка выполнения запроса: " . $e->getMessage(), 'error');
        }
        echo "<hr>";
    }
    
    // Проверяем, созданы ли таблицы
    echo "</div>";
    log_message("Проверка созданных таблиц:", 'info');
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $required_tables = ['users', 'categories', 'products', 'cart', 'cart_items', 'orders', 'order_items', 'feedback'];
    $missing_tables = array_diff($required_tables, $tables);
    
    if (empty($missing_tables)) {
        log_message("Все необходимые таблицы созданы успешно!", 'success');
    } else {
        log_message("Следующие таблицы не были созданы: " . implode(", ", $missing_tables), 'warning');
    }
    
    // Создаем тестового пользователя, если таблица пользователей существует
    if (in_array('users', $tables)) {
        try {
            // Проверяем, существуют ли тестовые пользователи
            $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE username IN ('admin', 'user1')");
            $users_count = $stmt->fetchColumn();
            
            if ($users_count == 0) {
                // Если пользователей нет, вставляем их
                $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, phone, role) VALUES
                ('admin', '$2y$10$rBl1wVFx5OYUO.rLgDlSb.7R/ghCFGCT.zYwY0Dk4VwQvyQd8iuIa', 'admin@cyberx.com', 'Администратор', '+375291234567', 'admin'),
                ('user1', '$2y$10$hJlIX9BL1Xq4/uhM9sE/4.9wehqnYFLBnwlgbX1FS3CJxLBJvKNHa', 'user1@example.com', 'Иван Иванов', '+375297654321', 'user')");
                $stmt->execute();
                log_message("Созданы тестовые пользователи admin и user1", 'success');
            } else if ($users_count < 2) {
                // Проверяем по отдельности, если один из пользователей существует, а другой - нет
                $stmt = $pdo->query("SELECT username FROM users WHERE username IN ('admin', 'user1')");
                $existing_users = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                if (!in_array('admin', $existing_users)) {
                    $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, phone, role) VALUES
                    ('admin', '$2y$10$rBl1wVFx5OYUO.rLgDlSb.7R/ghCFGCT.zYwY0Dk4VwQvyQd8iuIa', 'admin@cyberx.com', 'Администратор', '+375291234567', 'admin')");
                    $stmt->execute();
                    log_message("Создан тестовый пользователь admin", 'success');
                }
                
                if (!in_array('user1', $existing_users)) {
                    $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, phone, role) VALUES
                    ('user1', '$2y$10$hJlIX9BL1Xq4/uhM9sE/4.9wehqnYFLBnwlgbX1FS3CJxLBJvKNHa', 'user1@example.com', 'Иван Иванов', '+375297654321', 'user')");
                    $stmt->execute();
                    log_message("Создан тестовый пользователь user1", 'success');
                }
            } else {
                log_message("Тестовые пользователи уже существуют", 'info');
            }
        } catch (PDOException $e) {
            log_message("Ошибка при создании тестовых пользователей: " . $e->getMessage(), 'error');
        }
    }
    
    // Выводим ссылку для перехода на сайт
    echo "<div style='margin-top: 20px;'>";
    echo "<a href='index.php' style='display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px;'>Перейти на сайт</a>";
    echo "</div>";
    
} catch (Exception $e) {
    log_message("Произошла ошибка: " . $e->getMessage(), 'error');
}

// Функция для вывода формы входа
function display_login_form() {
    echo <<<HTML
    <div style="margin-top: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px;">
        <h3>Войти в систему</h3>
        <form method="post" action="login.php">
            <div style="margin-bottom: 10px;">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="admin@cyberx.com" style="padding: 5px; width: 100%; max-width: 300px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label for="password">Пароль:</label>
                <input type="password" id="password" name="password" value="admin" style="padding: 5px; width: 100%; max-width: 300px;">
            </div>
            <button type="submit" style="padding: 8px 15px; background-color: #2196F3; color: white; border: none; border-radius: 4px; cursor: pointer;">Войти</button>
        </form>
    </div>
HTML;
}

display_login_form();
?> 