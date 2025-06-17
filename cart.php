<?php
// Начинаем с session_start() до любого вывода
session_start();

// Подключаем необходимые файлы
require_once 'config/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/cart.php';

// Проверяем, нужно ли оформить заказ
if (isset($_POST['checkout']) && is_logged_in()) {
    if (checkout_cart()) {
        $_SESSION['success_message'] = 'Заказ успешно оформлен!';
        header('Location: profile.php?tab=orders');
        exit;
    } else {
        $_SESSION['error_message'] = 'Ошибка при оформлении заказа. Пожалуйста, попробуйте еще раз.';
    }
}

// Если перенаправления не было, продолжаем загрузку страницы
$page_title = 'Корзина';
$additional_css = 'cart.css';

include 'includes/header.php';

// Получаем содержимое корзины
$cart_items = get_cart_details($pdo);
$cart_total = get_cart_total();

// Отладочная информация
$debug_enabled = isset($_GET['debug']) && $_GET['debug'] == 1;
if ($debug_enabled) {
    echo "<div style='background:#f8f9fa; padding:15px; margin-bottom:20px; border:1px solid #ddd;'>";
    echo "<h3>Отладочная информация:</h3>";
    echo "<p>Авторизован: " . (is_logged_in() ? 'Да (ID: ' . $_SESSION['user_id'] . ')' : 'Нет') . "</p>";
    echo "<p>Количество товаров в корзине: " . count($cart_items) . "</p>";
    echo "<pre>" . htmlspecialchars(print_r($cart_items, true)) . "</pre>";
    echo "</div>";
}
?>

<head>
    <link rel="stylesheet" href="cart.css">
</head>

<div style="display: flex; flex-direction: column; min-height: 100vh;">
<div class="page-header">
    <h1>Корзина</h1>
    <a href="shop.php" class="back-button">Вернуться в магазин</a>
</div>

<div class="cart-container">
    <?php if (empty($cart_items)): ?>
        <div class="empty-cart">
            <p>Ваша корзина пуста</p>
            <a href="shop.php" class="button">Перейти в магазин</a>
        </div>
    <?php else: ?>
        <div class="cart-items-grid" style="
    width: 340px;
">
            <?php foreach ($cart_items as $item): ?>
            <div class="cart-item-card" data-product-id="<?php echo $item['product_id']; ?>">
                <div class="cart-item-image-container" style="
    justify-content: center;
    display: flex;
    margin-bottom: 12px;
    padding: 12px;
">
                    <img src="<?php echo !empty($item['image']) ? $item['image'] : 'IMG/product-default.jpg'; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-item-image" style="
    width: 200px;
    height: 150px;
">
                </div>
                <div class="cart-item-details">
                    <h3 class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                    <div class="cart-item-price"><?php echo format_price($item['price']); ?> BYN</div>
                    
                    <div class="cart-item-controls">
                        <div class="quantity-control">
                            <button type="button" class="quantity-btn minus-btn" data-action="decrease"><i class="fas fa-minus"></i></button>
                            <span class="quantity-value"><?php echo $item['quantity']; ?></span>
                            <button type="button" class="quantity-btn plus-btn" data-action="increase"><i class="fas fa-plus"></i></button>
                        </div>
                        
                        <div class="cart-item-subtotal">
                            <span>Итого: </span>
                            <span class="item-subtotal"><?php echo format_price($item['subtotal']); ?> BYN</span>
                        </div>
                        
                        <button type="button" class="remove-button" data-product-id="<?php echo $item['product_id']; ?>">
                            <i class="fas fa-trash"></i> Удалить
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="cart-summary">
            <div class="cart-total">
                <span>Итого:</span>
                <span class="cart-total-value"><?php echo format_price($cart_total); ?> BYN</span>
            </div>
            
            <div class="cart-actions">
                <button type="button" id="clearCartBtn" class="clear-button">
                    <i class="fas fa-trash"></i> Очистить корзину
                </button>
                
                <?php if (is_logged_in()): ?>
                    <form method="post" action="">
                        <button type="submit" name="checkout" class="checkout-button">
                            <i class="fas fa-credit-card"></i> Оформить заказ
                        </button>
                    </form>
                <?php else: ?>
                    <a href="login.php" class="checkout-button">
                        <i class="fas fa-sign-in-alt"></i> Войдите для оформления заказа
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Обработка кнопок изменения количества
    const quantityButtons = document.querySelectorAll('.quantity-btn');
    const removeButtons = document.querySelectorAll('.remove-button');
    const clearCartBtn = document.getElementById('clearCartBtn');
    const cartBadge = document.querySelector('.cart-badge');
    
    // Функция для обновления итоговой суммы
    function updateCartTotal(total) {
        const cartTotalElement = document.querySelector('.cart-total-value');
        if (cartTotalElement) {
            cartTotalElement.textContent = total;
        }
    }
    
    // Функция для форматирования цены (аналог PHP-функции format_price)
    function format_price_js(price) {
        return parseFloat(price).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, " ");
    }
    
    // Функция для вывода отладочной информации
    function debug(title, obj) {
        console.log('DEBUG ' + title + ':', obj);
    }
    
    // Обработка кнопок изменения количества
    if (quantityButtons) {
        quantityButtons.forEach(button => {
            button.addEventListener('click', function() {
                const card = this.closest('.cart-item-card');
                const productId = card.dataset.productId;
                const action = this.dataset.action;
                const quantityElement = card.querySelector('.quantity-value');
                let quantity = parseInt(quantityElement.textContent);
                
                if (action === 'increase') {
                    quantity++;
                } else if (action === 'decrease' && quantity > 1) {
                    quantity--;
                } else {
                    return; // Не делаем ничего, если количество <= 1 и нажата кнопка уменьшения
                }
                
                debug('Товар', {
                    id: productId,
                    action: action,
                    oldQuantity: parseInt(quantityElement.textContent),
                    newQuantity: quantity
                });
                
                // Отправляем AJAX-запрос для обновления количества
                const formData = new FormData();
                formData.append('action', 'update');
                formData.append('product_id', productId);
                formData.append('quantity', quantity);
                
                fetch('cart_ajax.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Обновляем отображение количества
                        quantityElement.textContent = quantity;
                        
                        // Используем данные о цене и подытоге из ответа сервера
                        if (data.product_subtotal) {
                            debug('Подытог из сервера', data.product_subtotal);
                            card.querySelector('.item-subtotal').textContent = data.product_subtotal;
                        } else {
                            // Резервный вариант, если сервер не вернул подытог
                            // Обновляем подытог для товара
                            const priceText = card.querySelector('.cart-item-price').textContent;
                            debug('Исходная цена (текст)', priceText);
                            
                            // Удаляем все пробелы и заменяем запятую на точку
                            const cleanPriceText = priceText.replace(' BYN', '').replace(/\s/g, '').replace(',', '.');
                            debug('Очищенная цена (текст)', cleanPriceText);
                            
                            const price = parseFloat(cleanPriceText);
                            debug('Цена (число)', price);
                            
                            const subtotal = price * quantity;
                            debug('Подытог', subtotal);
                            
                            const formattedSubtotal = format_price_js(subtotal) + ' BYN';
                            debug('Форматированный подытог', formattedSubtotal);
                            
                            card.querySelector('.item-subtotal').textContent = formattedSubtotal;
                        }
                        
                        // Обновляем общую сумму
                        updateCartTotal(data.cart_total);
                        
                        // Отладка - что вернул сервер
                        debug('Ответ сервера', data);
                        
                        // Обновляем счетчик в шапке
                        if (cartBadge) {
                            cartBadge.textContent = data.cart_count;
                            cartBadge.style.display = data.cart_count > 0 ? 'inline-flex' : 'none';
                        }
                    }
                })
                .catch(error => console.error('Ошибка:', error));
            });
        });
    }
    
    // Обработка кнопок удаления товара
    if (removeButtons) {
        removeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.productId;
                const card = this.closest('.cart-item-card');
                
                if (confirm('Вы уверены, что хотите удалить этот товар из корзины?')) {
                    // Отправляем AJAX-запрос для удаления товара
                    const formData = new FormData();
                    formData.append('action', 'remove');
                    formData.append('product_id', productId);
                    
                    fetch('cart_ajax.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Удаляем карточку товара
                            card.style.opacity = '0';
                            setTimeout(() => {
                                card.remove();
                                
                                // Обновляем общую сумму
                                updateCartTotal(data.cart_total);
                                
                                // Обновляем счетчик в шапке
                                if (cartBadge) {
                                    cartBadge.textContent = data.cart_count;
                                    cartBadge.style.display = data.cart_count > 0 ? 'inline-flex' : 'none';
                                }
                                
                                // Если корзина пуста, перезагружаем страницу
                                if (data.cart_count === 0) {
                                    location.reload();
                                }
                            }, 300);
                        }
                    })
                    .catch(error => console.error('Ошибка:', error));
                }
            });
        });
    }
    
    // Обработка кнопки очистки корзины
    if (clearCartBtn) {
        clearCartBtn.addEventListener('click', function() {
            if (confirm('Вы уверены, что хотите очистить корзину?')) {
                // Отправляем AJAX-запрос для очистки корзины
                const formData = new FormData();
                formData.append('action', 'clear');
                
                fetch('cart_ajax.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Перезагружаем страницу
                        location.reload();
                    }
                })
                .catch(error => console.error('Ошибка:', error));
            }
        });
    }
});
</script>

</div>
<?php include 'includes/footer.php'; ?> 