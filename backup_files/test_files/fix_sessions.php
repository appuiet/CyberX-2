<?php
// Проверяем и исправляем проблемы с сессиями

// Запускаем сессию
session_start();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Проверка конфигурации сессий - CYBERX</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Sofia+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="diagnostic-styles.css">
</head>
<body>
    <div class="diagnostic-container">
        <h1>Проверка и исправление проблем с сессиями</h1>

        <!-- Текущие настройки PHP -->
        <h2>Текущие настройки PHP</h2>
        <pre>
PHP version: <?php echo phpversion(); ?>

session.save_path: <?php echo ini_get('session.save_path'); ?>

session.gc_maxlifetime: <?php echo ini_get('session.gc_maxlifetime'); ?> seconds

session.cookie_lifetime: <?php echo ini_get('session.cookie_lifetime'); ?> seconds

session.use_cookies: <?php echo ini_get('session.use_cookies') ? "Yes" : "No"; ?>

session.use_only_cookies: <?php echo ini_get('session.use_only_cookies') ? "Yes" : "No"; ?>

session.cookie_secure: <?php echo ini_get('session.cookie_secure') ? "Yes" : "No"; ?>

session.cookie_httponly: <?php echo ini_get('session.cookie_httponly') ? "Yes" : "No"; ?>

session.cookie_samesite: <?php echo ini_get('session.cookie_samesite'); ?>

upload_tmp_dir: <?php echo ini_get('upload_tmp_dir'); ?>

memory_limit: <?php echo ini_get('memory_limit'); ?>

post_max_size: <?php echo ini_get('post_max_size'); ?>

max_execution_time: <?php echo ini_get('max_execution_time'); ?> seconds

display_errors: <?php echo ini_get('display_errors') ? "On" : "Off"; ?>

error_reporting: <?php echo ini_get('error_reporting'); ?>
        </pre>

        <!-- Проверка директории сессий -->
        <?php
        $session_path = ini_get('session.save_path');
        if (!empty($session_path)) {
            echo "<h2>Проверка директории сессий</h2>";
            echo "<p>Путь к директории сессий: <code>$session_path</code></p>";
            
            if (is_dir($session_path)) {
                echo "<div class='success-box'>";
                echo "<p class='success'>✓ Директория сессий существует.</p>";
                
                if (is_writable($session_path)) {
                    echo "<p class='success'>✓ Директория сессий доступна для записи.</p>";
                    echo "</div>";
                } else {
                    echo "</div>";
                    echo "<div class='error-box'>";
                    echo "<p class='error'>✗ Директория сессий НЕ доступна для записи!</p>";
                    echo "<p>Рекомендация: Установите права доступа 755 или 777 для директории сессий.</p>";
                    echo "</div>";
                }
            } else {
                echo "<div class='error-box'>";
                echo "<p class='error'>✗ Директория сессий НЕ существует!</p>";
                echo "<p>Рекомендация: Создайте директорию для сессий и установите соответствующие права доступа.</p>";
                echo "</div>";
            }
        }
        ?>

        <!-- Тест сессий -->
        <h2>Тест сессий</h2>
        <?php
        // Устанавливаем тестовое значение
        $_SESSION['test_value'] = "Тестовое значение - " . time();
        ?>
        <div class="info-box">
            <p>Установлено тестовое значение в сессию: <strong><?php echo $_SESSION['test_value']; ?></strong></p>
            <p>ID сессии: <code><?php echo session_id(); ?></code></p>
            <p>Имя сессии: <code><?php echo session_name(); ?></code></p>
        </div>

        <!-- Текущие куки -->
        <h2>Текущие куки</h2>
        <pre>
<?php print_r($_COOKIE); ?>
        </pre>

        <?php
        // Устанавливаем тестовую куку
        setcookie("test_cookie", "Тестовое значение куки", time() + 3600, "/");
        ?>
        <div class="info-box">
            <p>Установлена тестовая кука 'test_cookie'. Обновите страницу, чтобы увидеть её.</p>
        </div>

        <!-- Рекомендации по исправлению проблем -->
        <h2>Рекомендации по исправлению проблем с сессиями</h2>
        <ol>
            <li>Убедитесь, что в каждом файле, использующем сессии, вызывается <code>session_start()</code> в самом начале файла.</li>
            <li>Проверьте, что директория для хранения сессий существует и доступна для записи.</li>
            <li>Убедитесь, что браузер принимает куки.</li>
            <li>Проверьте, что в файле php.ini правильно настроены параметры сессий.</li>
            <li>Если используется кастомное хранилище сессий, убедитесь, что оно правильно настроено.</li>
            <li>Проверьте, что нет конфликтов с другими скриптами, которые могут изменять или уничтожать сессию.</li>
        </ol>

        <!-- Ссылки для тестирования -->
        <div class="links-section">
            <h2>Тестовые ссылки</h2>
            <ul>
                <li><a href="fix_sessions.php" class="btn">Обновить эту страницу</a> для проверки сохранения сессии</li>
                <li><a href="test_session.php" class="btn btn-outline">Тест сессии</a></li>
                <li><a href="check_database.php" class="btn btn-outline">Проверка базы данных</a></li>
                <li><a href="check_passwords.php" class="btn btn-outline">Проверка паролей</a></li>
                <li><a href="test_cart.php" class="btn btn-outline">Тестирование корзины</a></li>
                <li><a href="session_fix_instructions.php" class="btn btn-outline">Инструкции по исправлению проблем</a></li>
                <li><a href="login.php" class="btn">Страница входа</a></li>
                <li><a href="index.php" class="btn">Главная страница</a></li>
            </ul>
        </div>
    </div>
</body>
</html> 