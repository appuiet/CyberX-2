<?php
require_once 'config/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/cart.php';

// Запускаем сессию
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Проверяем, авторизован ли пользователь
if (!is_logged_in()) {
    echo "<h1>Тестирование заказов</h1>";
    echo "<p style='color:red;'>Для доступа к этой странице необходимо авторизоваться.</p>";
    echo "<p><a href='login.php'>Войти в систему</a></p>";
    exit;
}

// Обработка оформления заказа
if (isset($_POST['checkout'])) {
    $result = checkout_cart();
    if ($result) {
        echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 4px;'>";
        echo "<strong>Заказ успешно оформлен!</strong>";
        echo "</div>";
    } else {
        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 4px;'>";
        echo "<strong>Ошибка при оформлении заказа.</strong> Возможно, корзина пуста или произошла ошибка в базе данных.";
        echo "</div>";
    }
}

echo "<h1>Тестирование заказов</h1>";
echo "<p>Вы авторизованы как: <strong>" . $_SESSION['username'] . "</strong> (ID: " . $_SESSION['user_id'] . ")</p>";

// Получаем текущую корзину
$cart_items = get_cart_details($pdo);
$cart_total = get_cart_total();

echo "<h2>Текущая корзина:</h2>";
if (empty($cart_items)) {
    echo "<p>Ваша корзина пуста.</p>";
    echo "<p><a href='test_add_to_cart.php' style='display:inline-block; background:#4CAF50; color:white; padding:10px 15px; text-decoration:none; border-radius:4px;'>Добавить товары в корзину</a></p>";
} else {
    echo "<table style='width:100%; border-collapse:collapse;'>";
    echo "<tr style='background:#f5f5f5;'><th style='text-align:left; padding:10px; border:1px solid #ddd;'>Товар</th><th style='text-align:left; padding:10px; border:1px solid #ddd;'>Цена</th><th style='text-align:left; padding:10px; border:1px solid #ddd;'>Количество</th><th style='text-align:left; padding:10px; border:1px solid #ddd;'>Итого</th></tr>";
    
    foreach ($cart_items as $item) {
        echo "<tr>";
        echo "<td style='padding:10px; border:1px solid #ddd;'>" . htmlspecialchars($item['name']) . " (ID: " . $item['product_id'] . ")</td>";
        echo "<td style='padding:10px; border:1px solid #ddd;'>" . format_price($item['price']) . " BYN</td>";
        echo "<td style='padding:10px; border:1px solid #ddd;'>" . $item['quantity'] . "</td>";
        echo "<td style='padding:10px; border:1px solid #ddd;'>" . format_price($item['subtotal']) . " BYN</td>";
        echo "</tr>";
    }
    
    echo "<tr style='background:#f5f5f5;'>";
    echo "<td colspan='3' style='text-align:right; padding:10px; border:1px solid #ddd;'><strong>Общая сумма:</strong></td>";
    echo "<td style='padding:10px; border:1px solid #ddd;'><strong>" . format_price($cart_total) . " BYN</strong></td>";
    echo "</tr>";
    
    echo "</table>";
    
    echo "<form method='post' action='' style='margin-top:20px;'>";
    echo "<button type='submit' name='checkout' style='background:#4CAF50; color:white; border:none; padding:10px 20px; cursor:pointer; border-radius:4px;'>Оформить заказ</button>";
    echo "</form>";
}

// Получаем список заказов пользователя
echo "<h2>Ваши заказы:</h2>";
try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $orders = $stmt->fetchAll();
    
    if (empty($orders)) {
        echo "<p>У вас пока нет заказов.</p>";
    } else {
        foreach ($orders as $order) {
            echo "<div style='border:1px solid #ddd; padding:15px; margin-bottom:20px; border-radius:4px;'>";
            echo "<h3>Заказ #" . $order['id'] . "</h3>";
            echo "<p>Дата: " . date('d.m.Y H:i', strtotime($order['created_at'])) . "</p>";
            echo "<p>Статус: <span style='display:inline-block; padding:5px 10px; border-radius:4px; background-color:";
            
            switch ($order['status']) {
                case 'pending':
                    echo "#ffc107; color:#212529;'>В обработке";
                    break;
                case 'processing':
                    echo "#17a2b8; color:white;'>Комплектуется";
                    break;
                case 'completed':
                    echo "#28a745; color:white;'>Выполнен";
                    break;
                case 'cancelled':
                    echo "#dc3545; color:white;'>Отменен";
                    break;
                default:
                    echo "#6c757d; color:white;'>" . ucfirst($order['status']);
            }
            
            echo "</span></p>";
            echo "<p>Сумма заказа: <strong>" . format_price($order['total_amount']) . " BYN</strong></p>";
            
            // Получаем товары заказа
            $stmt = $pdo->prepare("
                SELECT oi.*, p.name, p.image 
                FROM order_items oi 
                LEFT JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = :order_id
            ");
            $stmt->bindParam(':order_id', $order['id'], PDO::PARAM_INT);
            $stmt->execute();
            $items = $stmt->fetchAll();
            
            echo "<h4>Товары в заказе:</h4>";
            if (empty($items)) {
                echo "<p>Информация о товарах отсутствует.</p>";
            } else {
                echo "<table style='width:100%; border-collapse:collapse;'>";
                echo "<tr style='background:#f5f5f5;'><th style='text-align:left; padding:10px; border:1px solid #ddd;'>Товар</th><th style='text-align:left; padding:10px; border:1px solid #ddd;'>Цена</th><th style='text-align:left; padding:10px; border:1px solid #ddd;'>Количество</th><th style='text-align:left; padding:10px; border:1px solid #ddd;'>Итого</th></tr>";
                
                foreach ($items as $item) {
                    echo "<tr>";
                    echo "<td style='padding:10px; border:1px solid #ddd;'>" . htmlspecialchars($item['name'] ?? 'Товар #' . $item['product_id']) . "</td>";
                    echo "<td style='padding:10px; border:1px solid #ddd;'>" . format_price($item['price']) . " BYN</td>";
                    echo "<td style='padding:10px; border:1px solid #ddd;'>" . $item['quantity'] . "</td>";
                    echo "<td style='padding:10px; border:1px solid #ddd;'>" . format_price($item['price'] * $item['quantity']) . " BYN</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
            }
            
            echo "</div>";
        }
    }
} catch (PDOException $e) {
    echo "<p style='color:red;'>Ошибка при получении заказов: " . $e->getMessage() . "</p>";
}

echo "<p><a href='test_add_to_cart.php' style='display:inline-block; background:#007bff; color:white; padding:10px 15px; text-decoration:none; border-radius:4px; margin-right:10px;'>Добавить товары в корзину</a>";
echo "<a href='cart.php' style='display:inline-block; background:#6c757d; color:white; padding:10px 15px; text-decoration:none; border-radius:4px;'>Перейти в корзину</a></p>";
?> 