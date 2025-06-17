<?php
require_once 'config/db_connect.php';
require_once 'includes/functions.php';

// Проверяем соединение с базой данных
echo "<h2>Проверка соединения с базой данных:</h2>";
if (isset($pdo)) {
    echo "<p style='color:green;'>Соединение с базой данных установлено успешно.</p>";
} else {
    echo "<p style='color:red;'>Ошибка соединения с базой данных!</p>";
    exit;
}

// Получаем список таблиц
echo "<h2>Список таблиц в базе данных:</h2>";
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "<p style='color:red;'>Таблицы не найдены. Возможно, база данных не инициализирована.</p>";
    } else {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>" . htmlspecialchars($table) . "</li>";
        }
        echo "</ul>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red;'>Ошибка при получении списка таблиц: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Проверяем таблицы для корзины
echo "<h2>Проверка таблиц для корзины:</h2>";
$requiredTables = ['cart', 'cart_items'];
foreach ($requiredTables as $table) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color:green;'>Таблица '$table' существует.</p>";
            
            // Проверяем структуру таблицы
            $stmt = $pdo->query("DESCRIBE $table");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "<p>Столбцы таблицы '$table': " . implode(", ", $columns) . "</p>";
        } else {
            echo "<p style='color:red;'>Таблица '$table' не существует!</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color:red;'>Ошибка при проверке таблицы '$table': " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// Проверяем сессию
echo "<h2>Проверка сессии:</h2>";
session_start();
echo "<p>ID сессии: " . session_id() . "</p>";
echo "<p>Текущее содержимое сессии:</p><pre>";
var_dump($_SESSION);
echo "</pre>";

// Проверяем статус авторизации
echo "<h2>Статус авторизации:</h2>";
if (is_logged_in()) {
    echo "<p style='color:green;'>Пользователь авторизован. ID пользователя: " . $_SESSION['user_id'] . "</p>";
} else {
    echo "<p style='color:orange;'>Пользователь не авторизован.</p>";
}

// Проверяем корзину
echo "<h2>Проверка корзины:</h2>";
include 'includes/cart.php';
$cart_items = get_cart_items();
echo "<p>Товаров в корзине: " . count($cart_items) . "</p>";
if (!empty($cart_items)) {
    echo "<ul>";
    foreach ($cart_items as $item) {
        echo "<li>Товар ID: " . $item['product_id'] . ", Количество: " . $item['quantity'] . ", Цена: " . $item['price'] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Корзина пуста.</p>";
}

// Проверяем выполнение запросов к таблицам корзины
echo "<h2>Проверка выполнения запросов:</h2>";
if (is_logged_in()) {
    try {
        $user_id = $_SESSION['user_id'];
        echo "<p>Проверка запроса к таблице cart:</p>";
        $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $cart = $stmt->fetch();
        if ($cart) {
            echo "<p style='color:green;'>Корзина найдена. ID: " . $cart['id'] . "</p>";
            
            echo "<p>Проверка запроса к таблице cart_items:</p>";
            $stmt = $pdo->prepare("SELECT * FROM cart_items WHERE cart_id = :cart_id");
            $stmt->bindParam(':cart_id', $cart['id'], PDO::PARAM_INT);
            $stmt->execute();
            $items = $stmt->fetchAll();
            
            if (!empty($items)) {
                echo "<p style='color:green;'>Найдено " . count($items) . " товаров в корзине.</p>";
            } else {
                echo "<p style='color:orange;'>Товары в корзине не найдены.</p>";
            }
        } else {
            echo "<p style='color:orange;'>Корзина для пользователя не найдена.</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color:red;'>Ошибка при выполнении запроса: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
?> 