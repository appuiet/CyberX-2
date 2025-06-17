<?php
$page_title = 'Оформление заказа';
$additional_css = 'shop.css';
include 'includes/header.php';

// Проверяем, авторизован ли пользователь
require_login();

// Получаем содержимое корзины
$cart_items = get_cart_details($pdo);
$cart_total = get_cart_total();

// Проверяем, не пуста ли корзина
if (empty($cart_items)) {
    $_SESSION['error_message'] = 'Ваша корзина пуста';
    header('Location: cart.php');
    exit;
}

// Получаем информацию о пользователе
$user = get_user_info($pdo, $_SESSION['user_id']);

// Обработка формы оформления заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Создаем заказ
    $order_id = create_order($pdo, $_SESSION['user_id'], $_SESSION['cart'], $cart_total);
    
    if ($order_id) {
        // Очищаем корзину
        clear_cart();
        
        $_SESSION['success_message'] = 'Заказ успешно оформлен';
        header('Location: order_success.php?order_id=' . $order_id);
        exit;
    } else {
        $_SESSION['error_message'] = 'Ошибка при оформлении заказа';
    }
}
?>

<header class="header">
    <img src="IMG/Logotip.png" alt="Центрированное изображение">
    <div class="header-text">Оформление заказа</div>
    <a href="cart.php" class="back-button">Назад</a>
</header>

<div class="checkout-container">
    <div class="checkout-summary">
        <h2>Ваш заказ</h2>
        <table class="checkout-table">
            <thead>
                <tr>
                    <th>Товар</th>
                    <th>Количество</th>
                    <th>Цена</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                <tr>
                    <td><?php echo $item['name']; ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo format_price($item['subtotal']); ?> BYN</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="text-right"><strong>Итого:</strong></td>
                    <td><strong><?php echo format_price($cart_total); ?> BYN</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
    
    <div class="checkout-form">
        <h2>Информация для доставки</h2>
        <form action="checkout.php" method="post">
            <div class="form-group">
                <label for="full_name">ФИО:</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo $user['full_name']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Телефон:</label>
                <input type="tel" id="phone" name="phone" value="<?php echo $user['phone']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="address">Адрес доставки:</label>
                <textarea id="address" name="address" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="payment_method">Способ оплаты:</label>
                <select id="payment_method" name="payment_method" required>
                    <option value="">Выберите способ оплаты</option>
                    <option value="cash">Наличными при получении</option>
                    <option value="card">Банковской картой при получении</option>
                    <option value="online">Онлайн-оплата</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="comment">Комментарий к заказу:</label>
                <textarea id="comment" name="comment"></textarea>
            </div>
            
            <button type="submit" class="checkout-button">Оформить заказ</button>
        </form>
    </div>
</div>

<?php
include 'includes/footer.php';
?> 