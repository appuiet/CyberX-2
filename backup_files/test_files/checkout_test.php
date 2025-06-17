<?php
// Начинаем с session_start() до любого вывода
session_start();

// Подключаем необходимые файлы
require_once 'config/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/cart.php';

// Обработка формы перед любым выводом HTML
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
$page_title = 'Оформление заказа';
$additional_css = 'shop.css';
include 'includes/header.php';

// Получаем содержимое корзины
$cart_items = get_cart_details($pdo);
$cart_total = get_cart_total();
?>

<div class="page-header">
    <h1>Оформление заказа</h1>
    <a href="cart.php" class="back-button">Вернуться в корзину</a>
</div>

<div class="checkout-container">
    <?php if (empty($cart_items)): ?>
        <div class="empty-checkout">
            <p>Ваша корзина пуста, нечего оформлять</p>
            <a href="shop.php" class="button">Перейти в магазин</a>
        </div>
    <?php else: ?>
        <div class="checkout-form-container">
            <h2>Информация о заказе</h2>
            <div class="cart-summary">
                <h3>Товары в корзине:</h3>
                <ul class="checkout-items">
                    <?php foreach ($cart_items as $item): ?>
                    <li>
                        <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                        <span class="item-quantity">x <?php echo $item['quantity']; ?></span>
                        <span class="item-price"><?php echo format_price($item['subtotal']); ?> BYN</span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <div class="checkout-total">
                    <span>Итого к оплате:</span>
                    <span class="total-price"><?php echo format_price($cart_total); ?> BYN</span>
                </div>
            </div>
            
            <form method="post" action="" class="checkout-form">
                <div class="form-group">
                    <label for="delivery_address">Адрес доставки:</label>
                    <textarea id="delivery_address" name="delivery_address" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="phone">Телефон для связи:</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                
                <div class="form-group">
                    <label>Способ оплаты:</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="payment_method" value="cash" checked>
                            Наличными при получении
                        </label>
                        <label>
                            <input type="radio" name="payment_method" value="card">
                            Картой при получении
                        </label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="cart.php" class="cancel-button">Отмена</a>
                    <button type="submit" name="checkout" class="checkout-button">
                        <i class="fas fa-credit-card"></i> Подтвердить заказ
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?> 