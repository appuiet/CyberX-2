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
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест AJAX для корзины</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1, h2 { color: #333; }
        .container { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        button { padding: 8px 15px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #45a049; }
        button:disabled { background-color: #cccccc; cursor: not-allowed; }
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 4px; overflow: auto; }
        .product { margin-bottom: 15px; padding: 10px; border: 1px solid #eee; border-radius: 4px; }
        .result { margin-top: 10px; padding: 10px; background-color: #f0f8ff; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>Тест AJAX-запросов к cart_ajax.php</h1>
    
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
        <h2>Добавление товара через AJAX</h2>
        
        <?php
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
                    echo "<button class='add-to-cart-btn' data-product-id='" . $product['id'] . "' data-price='" . $product['price'] . "'>Добавить в корзину</button>";
                    echo "<div class='result' id='result-" . $product['id'] . "'></div>";
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
        <div id="cart-content">
            <button id="refresh-cart">Обновить содержимое корзины</button>
            <div id="cart-items">Загрузка...</div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Обработчик для кнопок добавления в корзину
        const addButtons = document.querySelectorAll('.add-to-cart-btn');
        addButtons.forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const price = this.getAttribute('data-price');
                const resultDiv = document.getElementById('result-' + productId);
                
                this.disabled = true;
                this.textContent = 'Добавление...';
                
                // Создаем объект FormData для отправки данных
                const formData = new FormData();
                formData.append('action', 'add');
                formData.append('product_id', productId);
                formData.append('price', price);
                
                // Отправляем AJAX-запрос
                fetch('cart_ajax.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.text();
                })
                .then(text => {
                    console.log('Raw response:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error('Invalid JSON: ' + text);
                    }
                })
                .then(data => {
                    console.log('Parsed data:', data);
                    if (data.success) {
                        this.textContent = 'Добавлено';
                        resultDiv.innerHTML = `
                            <p style="color:green;">Товар успешно добавлен в корзину!</p>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        `;
                    } else {
                        this.textContent = 'Ошибка';
                        this.disabled = false;
                        resultDiv.innerHTML = `
                            <p style="color:red;">Ошибка при добавлении товара в корзину!</p>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        `;
                    }
                    // Обновляем содержимое корзины
                    refreshCart();
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.textContent = 'Ошибка';
                    this.disabled = false;
                    resultDiv.innerHTML = `
                        <p style="color:red;">Ошибка при выполнении запроса: ${error.message}</p>
                    `;
                });
            });
        });
        
        // Функция для обновления содержимого корзины
        function refreshCart() {
            const cartItemsDiv = document.getElementById('cart-items');
            cartItemsDiv.innerHTML = 'Загрузка...';
            
            // Получаем текущее состояние корзины
            const formData = new FormData();
            formData.append('action', 'get_count');
            
            fetch('cart_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Здесь мы можем получить подробную информацию о корзине, но для простоты просто выводим количество товаров
                    cartItemsDiv.innerHTML = `
                        <p>Количество товаров в корзине: <strong>${data.cart_count}</strong></p>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    `;
                } else {
                    cartItemsDiv.innerHTML = `
                        <p style="color:red;">Ошибка при получении содержимого корзины!</p>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    `;
                }
            })
            .catch(error => {
                cartItemsDiv.innerHTML = `
                    <p style="color:red;">Ошибка при выполнении запроса: ${error.message}</p>
                `;
            });
        }
        
        // Обработчик для кнопки обновления корзины
        document.getElementById('refresh-cart').addEventListener('click', refreshCart);
        
        // Обновляем корзину при загрузке страницы
        refreshCart();
    });
    </script>
</body>
</html> 