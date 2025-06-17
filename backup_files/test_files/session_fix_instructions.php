<?php
// Запускаем сессию
session_start();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Инструкции по исправлению проблем с сессиями</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        h2 {
            color: #2980b9;
            margin-top: 20px;
        }
        .step {
            background-color: #fff;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #3498db;
        }
        code {
            background-color: #f0f0f0;
            padding: 2px 5px;
            border-radius: 3px;
            font-family: Consolas, Monaco, 'Andale Mono', monospace;
        }
        pre {
            background-color: #f0f0f0;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        .warning {
            color: orange;
            font-weight: bold;
        }
        .links {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Инструкции по исправлению проблем с сессиями в PHP</h1>
        
        <p>Данная страница содержит инструкции по исправлению проблем с сессиями в PHP-приложении CyberX.</p>
        
        <h2>Выполненные исправления</h2>
        
        <div class="step">
            <h3>1. Правильная инициализация сессий</h3>
            <p>В файле <code>includes/header.php</code> добавлен вызов <code>session_start()</code> в самом начале файла:</p>
            <pre><?php highlight_string('<?php
// Запускаем сессию в начале файла
session_start();

require_once \'config/db_connect.php\';
// ... остальной код
?>'); ?></pre>
            <p>Это гарантирует, что сессия будет инициализирована на каждой странице, где подключается header.php.</p>
        </div>
        
        <div class="step">
            <h3>2. Удаление дублирующих вызовов session_start()</h3>
            <p>Из файла <code>includes/auth.php</code> удален вызов <code>session_start()</code>, так как он уже присутствует в header.php:</p>
            <pre><?php highlight_string('<?php
// Подключаем файлы с функциями
require_once \'functions.php\';

// ... остальной код
?>'); ?></pre>
        </div>
        
        <div class="step">
            <h3>3. Исправление файла logout.php</h3>
            <p>Файл <code>logout.php</code> обновлен для корректного уничтожения сессии:</p>
            <pre><?php highlight_string('<?php
// Запускаем сессию
session_start();

// Очищаем все данные сессии
$_SESSION = array();

// Уничтожаем куки сессии
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), \'\', time() - 42000, \'/\');
}

// Уничтожаем сессию
session_destroy();

// Перенаправляем на главную страницу
header(\'Location: index.php\');
exit;
?>'); ?></pre>
        </div>
        
        <div class="step">
            <h3>4. Исправление структуры HTML</h3>
            <p>Исправлена структура HTML в файле <code>index.php</code> для устранения дублирующих тегов:</p>
            <pre><?php highlight_string('<?php
$page_title = \'Главная\';
include \'includes/header.php\';
?>

<!-- Hero Section -->
<section class="hero">
    <h1>CYBER<span class="red">X</span></h1>
    <p>ТВОЙ ПУТЬ В КИБЕРСПОРТ!</p>
</section>

<!-- ... остальной контент ... -->

<?php
include \'includes/footer.php\';
?>'); ?></pre>
        </div>
        
        <h2>Дополнительные проверки</h2>
        
        <div class="step">
            <h3>1. Проверка работы сессий</h3>
            <p>Для проверки работы сессий созданы тестовые файлы:</p>
            <ul>
                <li><a href="test_session.php">test_session.php</a> - для проверки сохранения сессии между запросами</li>
                <li><a href="fix_sessions.php">fix_sessions.php</a> - для проверки конфигурации PHP и директории сессий</li>
                <li><a href="check_passwords.php">check_passwords.php</a> - для проверки хешированных паролей</li>
                <li><a href="check_database.php">check_database.php</a> - для проверки структуры базы данных</li>
                <li><a href="test_cart.php">test_cart.php</a> - для проверки работы корзины</li>
            </ul>
        </div>
        
        <div class="step">
            <h3>2. Проверка хешированных паролей</h3>
            <p>Для проверки и обновления хешированных паролей используйте файл <code>check_passwords.php</code>.</p>
            <p>Тестовые учетные записи:</p>
            <ul>
                <li>Администратор: логин <code>admin</code>, пароль <code>admin123</code></li>
                <li>Пользователь: логин <code>user1</code>, пароль <code>user123</code></li>
            </ul>
        </div>
        
        <h2>Рекомендации по дальнейшей оптимизации</h2>
        
        <div class="step">
            <h3>1. Проверка настроек PHP</h3>
            <p>Убедитесь, что в файле php.ini правильно настроены параметры сессий:</p>
            <pre>session.save_path = "путь_к_директории_сессий"
session.gc_maxlifetime = 1440
session.use_cookies = 1
session.use_only_cookies = 1</pre>
        </div>
        
        <div class="step">
            <h3>2. Проверка прав доступа</h3>
            <p>Убедитесь, что директория для хранения сессий существует и доступна для записи.</p>
        </div>
        
        <div class="step">
            <h3>3. Использование безопасных куки</h3>
            <p>Для повышения безопасности рекомендуется использовать следующие настройки:</p>
            <pre>session.cookie_httponly = 1
session.cookie_secure = 1 (только для HTTPS)
session.cookie_samesite = "Lax" или "Strict"</pre>
        </div>
        
        <div class="links">
            <h2>Полезные ссылки</h2>
            <ul>
                <li><a href="index.php">Главная страница</a></li>
                <li><a href="login.php">Страница входа</a></li>
                <li><a href="test_session.php">Тест сессий</a></li>
                <li><a href="check_passwords.php">Проверка паролей</a></li>
            </ul>
        </div>
        
        <p class="success">
            Текущий статус сессии: <?php echo isset($_SESSION) ? 'Сессия активна' : 'Сессия не активна'; ?>
        </p>
        <p>
            ID сессии: <?php echo session_id(); ?>
        </p>
    </div>
</body>
</html> 