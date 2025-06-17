<?php
$page_title = 'Детали заказа';
$additional_css = 'shop.css';
include 'includes/header.php';

// Проверяем, авторизован ли пользователь
require_login();

// Проверяем, передан ли ID заказа
if (!isset($_GET['order_id'])) {
    $_SESSION['error_message'] = 'Не указан ID заказа';
    header('Location: profile.php');
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
    $_SESSION['error_message'] = 'Заказ не найден или у вас нет доступа к нему';
    header('Location: profile.php');
    exit;
}

// Получаем детали заказа
$order_items = get_order_details($pdo, $order_id);
?>

<header class="header">
    <img src="IMG/Logotip.png" alt="Центрированное изображение">
    <div class="header-text">Детали заказа #<?php echo $order_id; ?></div>
    <a href="profile.php" class="back-button">Назад</a>
</header>

<div class="order-details-container">
    <div class="order-info">
        <h2>Информация о заказе</h2>
        <table class="order-info-table">
            <tr>
                <th>Номер заказа:</th>
                <td><?php echo $order_id; ?></td>
            </tr>
            <tr>
                <th>Дата заказа:</th>
                <td><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></td>
            </tr>
            <tr>
                <th>Статус заказа:</th>
                <td>
                    <?php
                    switch ($order['status']) {
                        case 'pending':
                            echo 'В обработке';
                            break;
                        case 'processing':
                            echo 'Обрабатывается';
                            break;
                        case 'completed':
                            echo 'Выполнен';
                            break;
                        case 'cancelled':
                            echo 'Отменен';
                            break;
                        default:
                            echo $order['status'];
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th>Сумма заказа:</th>
                <td><?php echo format_price($order['total_amount']); ?> BYN</td>
            </tr>
        </table>
    </div>
    
    <div class="order-items">
        <h2>Товары в заказе</h2>
        <table class="order-items-table">
            <thead>
                <tr>
                    <th>Изображение</th>
                    <th>Наименование</th>
                    <th>Цена</th>
                    <th>Количество</th>
                    <th>Итого</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_items as $item): ?>
                <tr>
                    <td><img src="IMG/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="order-item-image"></td>
                    <td><?php echo $item['name']; ?></td>
                    <td><?php echo format_price($item['price']); ?> BYN</td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo format_price($item['price'] * $item['quantity']); ?> BYN</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right"><strong>Итого:</strong></td>
                    <td><strong><?php echo format_price($order['total_amount']); ?> BYN</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php
include 'includes/footer.php';
?> 