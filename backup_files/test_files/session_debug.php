<?php
// Включаем отображение ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Запускаем сессию
session_start();

// Подключаем необходимые файлы
require_once 'config/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/cart.php';

// Проверяем наличие параметра действия
$action = isset($_GET['action']) ? $_GET['action'] : 'show';

// Очистка сессии
if ($action === 'clear') {
    session_unset();
    session_destroy();
    echo "<p>Сессия очищена</p>";
    echo "<a href='session_debug.php'>Вернуться к просмотру</a>";
    exit;
}

// Запуск новой сессии
if ($action === 'new') {
    session_regenerate_id(true);
    echo "<p>Создана новая сессия с ID: " . session_id() . "</p>";
    echo "<a href='session_debug.php'>Вернуться к просмотру</a>";
    exit;
}

// Тестовое добавление товара в корзину
if ($action === 'add_test') {
    $product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 1;
    $price = isset($_GET['price']) ? (float)$_GET['price'] : 100.00;
    $quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;
    
    $result = add_to_cart($product_id, $price, $quantity);
    
    echo "<p>Тестовое добавление товара в корзину:</p>";
    echo "<ul>";
    echo "<li>ID товара: $product_id</li>";
    echo "<li>Цена: $price</li>";
    echo "<li>Количество: $quantity</li>";
    echo "<li>Результат: " . ($result ? "Успешно" : "Ошибка") . "</li>";
    echo "</ul>";
    echo "<a href='session_debug.php'>Вернуться к просмотру</a>";
    exit;
}

// Проверка корзины
if ($action === 'check_cart') {
    $cart_items = get_cart_items();
    $cart_count = get_cart_count();
    $cart_total = get_cart_total();
    
    echo "<h2>Информация о корзине</h2>";
    echo "<p>Количество товаров: $cart_count</p>";
    echo "<p>Общая сумма: $cart_total BYN</p>";
    
    if (empty($cart_items)) {
        echo "<p>Корзина пуста</p>";
    } else {
        echo "<h3>Товары в корзине:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID товара</th><th>Количество</th><th>Цена</th><th>Сумма</th></tr>";
        
        foreach ($cart_items as $product_id => $item) {
            echo "<tr>";
            echo "<td>$product_id</td>";
            echo "<td>{$item['quantity']}</td>";
            echo "<td>{$item['price']} BYN</td>";
            echo "<td>" . ($item['price'] * $item['quantity']) . " BYN</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    echo "<a href='session_debug.php'>Вернуться к просмотру</a>";
    exit;
}

// Получаем информацию о PHP
$php_version = phpversion();
$session_id = session_id();
$session_name = session_name();
$session_status = session_status();
$session_status_text = [
    PHP_SESSION_DISABLED => 'Сессии отключены',
    PHP_SESSION_NONE => 'Сессии включены, но нет активной сессии',
    PHP_SESSION_ACTIVE => 'Сессии включены, и есть активная сессия',
][$session_status];

// Получаем информацию о пользователе
$is_logged_in = is_logged_in();
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Нет';
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Нет';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отладка сессий и корзины</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
        h1, h2, h3 { color: #333; }
        .container { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 4px; overflow: auto; }
        table { border-collapse: collapse; width: 100%; }
        th, td { text-align: left; padding: 8px; }
        th { background-color: #f2f2f2; }
        .actions { margin-top: 20px; }
        .actions a { display: inline-block; margin-right: 10px; padding: 5px 10px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 3px; }
        .actions a:hover { background-color: #45a049; }
        .test-form { margin-top: 20px; padding: 15px; background-color: #f9f9f9; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Отладка сессий и корзины</h1>
    
    <div class="container">
        <h2>Информация о PHP и сессии</h2>
        <table>
            <tr><th>PHP версия</th><td><?php echo $php_version; ?></td></tr>
            <tr><th>ID сессии</th><td><?php echo $session_id; ?></td></tr>
            <tr><th>Имя сессии</th><td><?php echo $session_name; ?></td></tr>
            <tr><th>Статус сессии</th><td><?php echo $session_status_text; ?> (<?php echo $session_status; ?>)</td></tr>
            <tr><th>Путь сохранения сессий</th><td><?php echo ini_get('session.save_path'); ?></td></tr>
            <tr><th>Cookie параметры</th><td>path=<?php echo ini_get('session.cookie_path'); ?>, domain=<?php echo ini_get('session.cookie_domain'); ?>, secure=<?php echo ini_get('session.cookie_secure'); ?></td></tr>
        </table>
    </div>
    
    <div class="container">
        <h2>Информация о пользователе</h2>
        <table>
            <tr><th>Авторизован</th><td><?php echo $is_logged_in ? 'Да' : 'Нет'; ?></td></tr>
            <tr><th>ID пользователя</th><td><?php echo $user_id; ?></td></tr>
            <tr><th>Имя пользователя</th><td><?php echo $username; ?></td></tr>
            <tr><th>Роль</th><td><?php echo is_admin() ? 'Администратор' : 'Пользователь'; ?></td></tr>
        </table>
    </div>
    
    <div class="container">
        <h2>Содержимое сессии</h2>
        <pre><?php print_r($_SESSION); ?></pre>
    </div>
    
    <div class="container">
        <h2>Тестирование корзины</h2>
        <div class="test-form">
            <h3>Добавить тестовый товар в корзину</h3>
            <form action="session_debug.php" method="get">
                <input type="hidden" name="action" value="add_test">
                <table>
                    <tr>
                        <th>ID товара:</th>
                        <td><input type="number" name="product_id" value="1" min="1" required></td>
                    </tr>
                    <tr>
                        <th>Цена:</th>
                        <td><input type="number" name="price" value="100.00" min="0" step="0.01" required></td>
                    </tr>
                    <tr>
                        <th>Количество:</th>
                        <td><input type="number" name="quantity" value="1" min="1" required></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><input type="submit" value="Добавить в корзину"></td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
    
    <div class="actions">
        <a href="session_debug.php?action=check_cart">Проверить корзину</a>
        <a href="session_debug.php?action=clear">Очистить сессию</a>
        <a href="session_debug.php?action=new">Создать новую сессию</a>
        <a href="ajax_test.php">Тест AJAX</a>
        <a href="direct_form_test.php">Тест прямой формы</a>
        <a href="shop.php">Перейти в магазин</a>
    </div>
</body>
</html> 