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

echo "<h1>Тест добавления товара в корзину</h1>";

echo "<h2>Статус авторизации:</h2>";
if ($is_logged_in) {
    echo "<p style='color:green;'>Вы авторизованы как: " . $_SESSION['username'] . " (ID: " . $_SESSION['user_id'] . ")</p>";
} else {
    echo "<p style='color:orange;'>Вы не авторизованы. Товар будет добавлен в сессионную корзину.</p>";
    echo "<p><a href='login.php'>Войти в систему</a> для сохранения корзины в базе данных.</p>";
}

// Получаем список продуктов для выбора
echo "<h2>Выберите товар для добавления в корзину:</h2>";

try {
    $stmt = $pdo->query("SELECT id, name, price, image FROM products LIMIT 10");
    $products = $stmt->fetchAll();
    
    if (empty($products)) {
        echo "<p style='color:red;'>Товары не найдены в базе данных!</p>";
    } else {
        echo "<div style='display:flex; flex-wrap:wrap; gap:20px;'>";
        foreach ($products as $product) {
            echo "<div style='border:1px solid #ddd; padding:15px; border-radius:5px; width:250px;'>";
            echo "<h3>" . htmlspecialchars($product['name']) . "</h3>";
            if (!empty($product['image'])) {
                echo "<img src='" . htmlspecialchars($product['image']) . "' style='max-width:100%; height:auto;' alt='" . htmlspecialchars($product['name']) . "'>";
            } else {
                echo "<div style='background:#eee; height:150px; display:flex; align-items:center; justify-content:center;'>Нет изображения</div>";
            }
            echo "<p>Цена: " . format_price($product['price']) . " BYN</p>";
            echo "<form method='post' action=''>";
            echo "<input type='hidden' name='product_id' value='" . $product['id'] . "'>";
            echo "<input type='hidden' name='price' value='" . $product['price'] . "'>";
            echo "<label>Количество: <input type='number' name='quantity' value='1' min='1' max='10' style='width:60px;'></label><br><br>";
            echo "<button type='submit' name='add_to_cart' style='background:#4CAF50; color:white; border:none; padding:8px 15px; cursor:pointer; border-radius:4px;'>Добавить в корзину</button>";
            echo "</form>";
            echo "</div>";
        }
        echo "</div>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red;'>Ошибка при получении товаров: " . $e->getMessage() . "</p>";
}

// Проверяем отправку формы
if (isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $price = (float)$_POST['price'];
    
    echo "<h2>Результат добавления товара:</h2>";
    
    // Проверяем существование товара
    $stmt = $pdo->prepare("SELECT id, name FROM products WHERE id = :product_id");
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch();
    
    if (!$product) {
        echo "<p style='color:red;'>Товар с ID $product_id не найден!</p>";
    } else {
        echo "<p>Добавляем товар: <strong>" . htmlspecialchars($product['name']) . "</strong></p>";
        echo "<p>Количество: <strong>$quantity</strong></p>";
        echo "<p>Цена: <strong>" . format_price($price) . " BYN</strong></p>";
        
        // Добавляем товар в корзину и отображаем результат
        $result = add_to_cart($product_id, $price, $quantity);
        
        if ($result) {
            echo "<p style='color:green;'>Товар успешно добавлен в корзину!</p>";
            
            // Отображаем содержимое корзины
            echo "<h3>Текущее содержимое корзины:</h3>";
            $cart_items = get_cart_items();
            
            if (empty($cart_items)) {
                echo "<p>Корзина пуста.</p>";
            } else {
                echo "<table style='width:100%; border-collapse:collapse;'>";
                echo "<tr style='background:#f5f5f5;'><th style='text-align:left; padding:10px; border:1px solid #ddd;'>Товар ID</th><th style='text-align:left; padding:10px; border:1px solid #ddd;'>Количество</th><th style='text-align:left; padding:10px; border:1px solid #ddd;'>Цена</th><th style='text-align:left; padding:10px; border:1px solid #ddd;'>Сумма</th></tr>";
                
                foreach ($cart_items as $item) {
                    echo "<tr>";
                    echo "<td style='padding:10px; border:1px solid #ddd;'>" . $item['product_id'] . "</td>";
                    echo "<td style='padding:10px; border:1px solid #ddd;'>" . $item['quantity'] . "</td>";
                    echo "<td style='padding:10px; border:1px solid #ddd;'>" . format_price($item['price']) . " BYN</td>";
                    echo "<td style='padding:10px; border:1px solid #ddd;'>" . format_price($item['price'] * $item['quantity']) . " BYN</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
                
                // Если пользователь авторизован, проверяем данные в базе
                if ($is_logged_in) {
                    echo "<h3>Проверка данных в базе данных:</h3>";
                    
                    try {
                        // Получаем ID корзины пользователя
                        $stmt = $pdo->prepare("SELECT id FROM cart WHERE user_id = :user_id");
                        $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                        $stmt->execute();
                        $cart = $stmt->fetch();
                        
                        if ($cart) {
                            echo "<p>Найдена корзина в БД с ID: " . $cart['id'] . "</p>";
                            
                            // Получаем товары из корзины
                            $stmt = $pdo->prepare("
                                SELECT ci.*, p.name 
                                FROM cart_items ci 
                                JOIN products p ON ci.product_id = p.id 
                                WHERE ci.cart_id = :cart_id
                            ");
                            $stmt->bindParam(':cart_id', $cart['id'], PDO::PARAM_INT);
                            $stmt->execute();
                            $db_items = $stmt->fetchAll();
                            
                            if (empty($db_items)) {
                                echo "<p style='color:red;'>Товары в корзине не найдены в БД!</p>";
                            } else {
                                echo "<table style='width:100%; border-collapse:collapse;'>";
                                echo "<tr style='background:#f5f5f5;'><th style='text-align:left; padding:10px; border:1px solid #ddd;'>ID</th><th style='text-align:left; padding:10px; border:1px solid #ddd;'>Товар</th><th style='text-align:left; padding:10px; border:1px solid #ddd;'>Количество</th><th style='text-align:left; padding:10px; border:1px solid #ddd;'>Цена</th></tr>";
                                
                                foreach ($db_items as $item) {
                                    echo "<tr>";
                                    echo "<td style='padding:10px; border:1px solid #ddd;'>" . $item['id'] . "</td>";
                                    echo "<td style='padding:10px; border:1px solid #ddd;'>" . htmlspecialchars($item['name']) . " (ID: " . $item['product_id'] . ")</td>";
                                    echo "<td style='padding:10px; border:1px solid #ddd;'>" . $item['quantity'] . "</td>";
                                    echo "<td style='padding:10px; border:1px solid #ddd;'>" . format_price($item['price']) . " BYN</td>";
                                    echo "</tr>";
                                }
                                
                                echo "</table>";
                            }
                        } else {
                            echo "<p style='color:red;'>Корзина не найдена в БД для пользователя с ID " . $_SESSION['user_id'] . "!</p>";
                        }
                    } catch (PDOException $e) {
                        echo "<p style='color:red;'>Ошибка при проверке данных в БД: " . $e->getMessage() . "</p>";
                    }
                }
            }
            
            echo "<p><a href='cart.php' style='display:inline-block; background:#4CAF50; color:white; padding:10px 15px; text-decoration:none; border-radius:4px;'>Перейти в корзину</a></p>";
        } else {
            echo "<p style='color:red;'>Ошибка при добавлении товара в корзину!</p>";
        }
    }
}

// Добавляем кнопку для очистки корзины
echo "<hr>";
echo "<form method='post' action=''>";
echo "<button type='submit' name='clear_cart' style='background:#f44336; color:white; border:none; padding:8px 15px; cursor:pointer; border-radius:4px; margin-top:20px;'>Очистить корзину</button>";
echo "</form>";

// Очистка корзины
if (isset($_POST['clear_cart'])) {
    clear_cart();
    echo "<p style='color:green;'>Корзина очищена!</p>";
    echo "<script>setTimeout(function() { window.location.reload(); }, 1000);</script>";
}
?> 