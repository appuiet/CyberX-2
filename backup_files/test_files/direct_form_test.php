<?php
require_once 'config/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/cart.php';

// Запускаем сессию
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Статус авторизации
$is_logged_in = is_logged_in();

// Обработка формы
$form_submitted = false;
$result_message = '';

if (isset($_POST['add_to_cart'])) {
    $form_submitted = true;
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $price = (float)$_POST['price'];
    
    // Добавляем товар в корзину
    $result = add_to_cart($product_id, $price, $quantity);
    
    if ($result) {
        $result_message = '<p style="color:green;">Товар успешно добавлен в корзину!</p>';
    } else {
        $result_message = '<p style="color:red;">Ошибка при добавлении товара в корзину!</p>';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест прямой отправки формы</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1, h2 { color: #333; }
        .container { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        button, input[type="submit"] { padding: 8px 15px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover, input[type="submit"]:hover { background-color: #45a049; }
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 4px; overflow: auto; }
        .product { margin-bottom: 15px; padding: 10px; border: 1px solid #eee; border-radius: 4px; }
        .result { margin-top: 15px; padding: 10px; background-color: #f0f8ff; border-radius: 4px; }
        input[type="number"] { width: 60px; padding: 5px; }
    </style>
</head>
<body>
    <h1>Тест прямой отправки формы для добавления в корзину</h1>
    
    <div class="container">
        <h2>Статус авторизации</h2>
        <?php if ($is_logged_in): ?>
            <p style="color:green;">Вы авторизованы как: <?php echo htmlspecialchars($_SESSION['username']); ?> (ID: <?php echo $_SESSION['user_id']; ?>)</p>
        <?php else: ?>
            <p style="color:orange;">Вы не авторизованы. Товар будет добавлен в сессионную корзину.</p>
            <p><a href="login.php">Войти в систему</a> для сохранения корзины в базе данных.</p>
        <?php endif; ?>
    </div>
    
    <div class="container">
        <h2>Добавление товара через форму</h2>
        
        <?php
        // Выводим результат отправки формы
        if ($form_submitted) {
            echo '<div class="result">';
            echo $result_message;
            echo '<p>Количество товаров в корзине: <strong>' . get_cart_count() . '</strong></p>';
            echo '</div>';
        }
        
        // Получаем несколько товаров для тестирования
        try {
            $stmt = $pdo->query("SELECT id, name, price, image FROM products LIMIT 3");
            $products = $stmt->fetchAll();
            
            if (empty($products)) {
                echo "<p>Товары не найдены в базе данных!</p>";
            } else {
                foreach ($products as $product) {
                    echo "<div class='product'>";
                    echo "<h3>" . htmlspecialchars($product['name']) . "</h3>";
                    echo "<p>ID: " . $product['id'] . ", Цена: " . format_price($product['price']) . " BYN</p>";
                    echo "<form method='post' action=''>";
                    echo "<input type='hidden' name='product_id' value='" . $product['id'] . "'>";
                    echo "<input type='hidden' name='price' value='" . $product['price'] . "'>";
                    echo "<label>Количество: <input type='number' name='quantity' value='1' min='1' max='10'></label> ";
                    echo "<input type='submit' name='add_to_cart' value='Добавить в корзину'>";
                    echo "</form>";
                    echo "</div>";
                }
            }
        } catch (PDOException $e) {
            echo "<p>Ошибка при получении товаров: " . $e->getMessage() . "</p>";
        }
        ?>
    </div>
    
    <div class="container">
        <h2>Текущее содержимое корзины</h2>
        <?php
        $cart_items = get_cart_items();
        
        if (empty($cart_items)) {
            echo "<p>Корзина пуста.</p>";
        } else {
            echo "<table style='width:100%; border-collapse:collapse;'>";
            echo "<tr style='background:#f5f5f5;'><th style='text-align:left; padding:10px; border:1px solid #ddd;'>Товар ID</th><th style='text-align:left; padding:10px; border:1px solid #ddd;'>Количество</th><th style='text-align:left; padding:10px; border:1px solid #ddd;'>Цена</th><th style='text-align:left; padding:10px; border:1px solid #ddd;'>Сумма</th></tr>";
            
            foreach ($cart_items as $product_id => $item) {
                echo "<tr>";
                echo "<td style='padding:10px; border:1px solid #ddd;'>" . $product_id . "</td>";
                echo "<td style='padding:10px; border:1px solid #ddd;'>" . $item['quantity'] . "</td>";
                echo "<td style='padding:10px; border:1px solid #ddd;'>" . format_price($item['price']) . " BYN</td>";
                echo "<td style='padding:10px; border:1px solid #ddd;'>" . format_price($item['price'] * $item['quantity']) . " BYN</td>";
                echo "</tr>";
            }
            
            echo "</table>";
            
            echo "<div style='margin-top:15px;'>";
            echo "<p>Общая сумма: <strong>" . format_price(get_cart_total()) . " BYN</strong></p>";
            echo "<form method='post' action=''>";
            echo "<input type='submit' name='clear_cart' value='Очистить корзину' style='background-color:#f44336;'>";
            echo "</form>";
            echo "</div>";
        }
        
        // Обработка очистки корзины
        if (isset($_POST['clear_cart'])) {
            clear_cart();
            echo "<script>window.location.reload();</script>";
        }
        ?>
    </div>
    
    <div class="container">
        <h2>Ссылки на другие тесты</h2>
        <p><a href="ajax_test.php">Тест AJAX-запросов</a></p>
        <p><a href="test_add_to_cart.php">Тест добавления товаров в корзину</a></p>
        <p><a href="shop.php">Перейти в магазин</a></p>
        <p><a href="cart.php">Перейти в корзину</a></p>
    </div>
</body>
</html> 