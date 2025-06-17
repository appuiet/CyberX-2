<?php
// Запускаем сессию
session_start();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тестирование сессий - CYBERX</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Sofia+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="diagnostic-styles.css">
</head>
<body>
    <div class="diagnostic-container">
        <h1>Тестирование сессии</h1>

        <!-- Содержимое сессии -->
        <h2>Текущее состояние сессии</h2>
        <pre>
SESSION: 
<?php print_r($_SESSION); ?>
        </pre>

        <?php
        // Устанавливаем тестовую переменную в сессию
        $_SESSION['test'] = 'Тестовое значение - ' . time();
        ?>

        <div class="info-box">
            <p>Установлена тестовая переменная в сессию: <?php echo $_SESSION['test']; ?></p>
            <p><a href='test_session.php'>Обновить страницу</a> для проверки сохранения сессии</p>
        </div>

        <!-- Проверка сохранения сессии -->
        <?php
        if (isset($_SESSION['test_time'])) {
            $time_diff = time() - $_SESSION['test_time'];
            echo "<div class='success-box'>";
            echo "<p class='success'>Сессия сохраняется! Прошло {$time_diff} секунд с момента установки.</p>";
            echo "</div>";
        } else {
            $_SESSION['test_time'] = time();
            echo "<div class='info-box'>";
            echo "<p>Установлено время для проверки сессии.</p>";
            echo "</div>";
        }
        ?>

        <!-- Конфигурация PHP для сессий -->
        <h2>Конфигурация сессий PHP</h2>
        <pre>
session.save_path: <?php echo ini_get('session.save_path'); ?>

session.name: <?php echo ini_get('session.name'); ?>

session.cookie_path: <?php echo ini_get('session.cookie_path'); ?>

session.cookie_domain: <?php echo ini_get('session.cookie_domain'); ?>

session.cookie_secure: <?php echo ini_get('session.cookie_secure'); ?>

session.cookie_httponly: <?php echo ini_get('session.cookie_httponly'); ?>

session.use_cookies: <?php echo ini_get('session.use_cookies'); ?>

session.use_only_cookies: <?php echo ini_get('session.use_only_cookies'); ?>

session.gc_maxlifetime: <?php echo ini_get('session.gc_maxlifetime'); ?>
        </pre>

        <!-- Информация о куках -->
        <h2>Куки</h2>
        <pre>
<?php print_r($_COOKIE); ?>
        </pre>

        <!-- Ссылки для тестирования -->
        <div class="links-section">
            <h2>Тестовые ссылки</h2>
            <ul>
                <li><a href="check_database.php" class="btn btn-outline">Проверка базы данных</a></li>
                <li><a href="check_passwords.php" class="btn btn-outline">Проверка паролей</a></li>
                <li><a href="fix_sessions.php" class="btn btn-outline">Проверка конфигурации сессий</a></li>
                <li><a href="test_cart.php" class="btn btn-outline">Тестирование корзины</a></li>
                <li><a href="session_fix_instructions.php" class="btn btn-outline">Инструкции по исправлению проблем</a></li>
                <li><a href="login.php" class="btn">Страница входа</a></li>
                <li><a href="index.php" class="btn">Главная страница</a></li>
                <li><a href="logout.php" class="btn">Выход</a></li>
            </ul>
        </div>
    </div>
</body>
</html> 