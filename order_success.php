<?php
$page_title = 'Заказ оформлен';
$additional_css = 'shop.css';
include 'includes/header.php';

// Проверяем, авторизован ли пользователь
require_login();

// Проверяем, передан ли ID заказа
if (!isset($_GET['order_id'])) {
    header('Location: index.php');
    exit;
}

$order_id = (int)$_GET['order_id'];

// Получаем информацию о заказе
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = :order_id AND user_id = :user_id");
$stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
$stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->execute();
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['error_message'] = 'Заказ не найден';
    header('Location: index.php');
    exit;
}

// Получаем детали заказа
$order_items = get_order_details($pdo, $order_id);
?>

<header class="header">
    <img src="IMG/Logotip.png" alt="Центрированное изображение">
    <div class="header-text">Заказ оформлен</div>
    <a href="index.php" class="back-button">На главную</a>
</header>

<div class="success-container">
    <div class="success-message">
        <h2>Заказ успешно оформлен!</h2>
        <p>Номер вашего заказа: <strong><?php echo $order_id; ?></strong></p>
        <p>Статус заказа: <strong><?php echo ($order['status'] === 'pending') ? 'В обработке' : $order['status']; ?></strong></p>
        <p>Дата заказа: <strong><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></strong></p>
        <p>Сумма заказа: <strong><?php echo format_price($order['total_amount']); ?> BYN</strong></p>
    </div>
    
    <div class="order-details">
        <h3>Состав заказа:</h3>
        <table class="order-table">
            <thead>
                <tr>
                    <th>Товар</th>
                    <th>Количество</th>
                    <th>Цена</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_items as $item): ?>
                <tr>
                    <td><?php echo $item['name']; ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo format_price($item['price']); ?> BYN</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="text-right"><strong>Итого:</strong></td>
                    <td><strong><?php echo format_price($order['total_amount']); ?> BYN</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
    
    <div class="order-actions">
        <a href="shop.php" class="button">Продолжить покупки</a>
        <a href="profile.php" class="button">Мои заказы</a>
    </div>
</div>

<?php
include 'includes/footer.php';
?> 