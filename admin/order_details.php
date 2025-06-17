<?php
$page_title = 'Детали заказа';
require_once '../config/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Проверяем права доступа
require_admin();

// Проверяем, передан ли ID заказа
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = 'Неверный ID заказа';
    header('Location: orders.php');
    exit;
}

$order_id = (int)$_GET['id'];

// Получаем информацию о заказе
$stmt = $pdo->prepare("SELECT o.*, u.username, u.email, u.phone, u.full_name FROM orders o 
                      LEFT JOIN users u ON o.user_id = u.id 
                      WHERE o.id = :order_id");
$stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
$stmt->execute();
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['error_message'] = 'Заказ не найден';
    header('Location: orders.php');
    exit;
}

// Получаем элементы заказа
$stmt = $pdo->prepare("SELECT oi.*, p.name, p.image, p.price as current_price 
                      FROM order_items oi 
                      LEFT JOIN products p ON oi.product_id = p.id 
                      WHERE oi.order_id = :order_id");
$stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
$stmt->execute();
$order_items = $stmt->fetchAll();

// Обработка редактирования заказа
if (isset($_POST['update_order'])) {
    $status = $_POST['status'];
    $shipping_address = $_POST['shipping_address'];
    $payment_method = $_POST['payment_method'];
    $tracking_number = $_POST['tracking_number'];
    $comment = $_POST['comment'];
    
    $stmt = $pdo->prepare("UPDATE orders SET 
                          status = :status, 
                          shipping_address = :shipping_address, 
                          payment_method = :payment_method, 
                          tracking_number = :tracking_number, 
                          comment = :comment, 
                          updated_at = NOW() 
                          WHERE id = :order_id");
    
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':shipping_address', $shipping_address, PDO::PARAM_STR);
    $stmt->bindParam(':payment_method', $payment_method, PDO::PARAM_STR);
    $stmt->bindParam(':tracking_number', $tracking_number, PDO::PARAM_STR);
    $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
    $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Заказ успешно обновлен';
        
        // Отправляем уведомление клиенту, если заказ изменил статус
        if ($order['status'] != $status) {
            // Здесь код для отправки письма
            // send_order_status_notification($order['email'], $order_id, $status);
        }
        
        // Перезагружаем страницу для обновления данных
        header("Location: order_details.php?id=$order_id");
        exit;
    } else {
        $_SESSION['error_message'] = 'Ошибка при обновлении заказа';
    }
}

// Получаем историю изменений заказа, если такая таблица существует
$order_history = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM order_history WHERE order_id = :order_id ORDER BY created_at DESC");
    $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $stmt->execute();
    $order_history = $stmt->fetchAll();
} catch (PDOException $e) {
    // Если таблицы не существует, просто игнорируем
}

// Определяем статусы заказа
$statuses = [
    'pending' => 'В обработке',
    'processing' => 'Обрабатывается',
    'shipping' => 'Отправлен',
    'delivered' => 'Доставлен',
    'completed' => 'Выполнен',
    'cancelled' => 'Отменен',
    'refunded' => 'Возвращен'
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $page_title; ?> - CYBERX</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        .order-details-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        
        .order-summary, .order-actions, .order-history, .order-items {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .order-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .info-group {
            margin-bottom: 15px;
        }
        
        .info-label {
            font-weight: bold;
            margin-bottom: 5px;
            color: #666;
        }
        
        .info-value {
            font-size: 16px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            color: #fff;
        }
        
        .status-pending { background-color: #ffc107; }
        .status-processing { background-color: #17a2b8; }
        .status-shipping { background-color: #6f42c1; }
        .status-delivered { background-color: #28a745; }
        .status-completed { background-color: #28a745; }
        .status-cancelled { background-color: #dc3545; }
        .status-refunded { background-color: #fd7e14; }
        
        .order-items table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .order-items th, 
        .order-items td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .order-items th {
            background-color: #f9f9f9;
        }
        
        .order-items tfoot td {
            font-weight: bold;
        }
        
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .product-name {
            font-weight: bold;
        }
        
        .history-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .history-item:last-child {
            border-bottom: none;
        }
        
        .history-date {
            font-size: 12px;
            color: #666;
        }
        
        .print-button {
            background-color: #6c757d;
            margin-right: 10px;
        }
        
        @media screen and (max-width: 768px) {
            .order-details-container {
                grid-template-columns: 1fr;
            }
        }
        
        .price-change {
            font-size: 12px;
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-sidebar">
            <div class="admin-logo">
                <img src="../IMG/logo.jpg" alt="Логотип" class="logo-image">
                <h2>Панель администратора</h2>
            </div>
            <nav class="admin-menu">
                <ul>
                    <li><a href="index.php">Главная</a></li>
                    <li><a href="products.php">Товары</a></li>
                    <li><a href="categories.php">Категории</a></li>
                    <li><a href="orders.php" class="active">Заказы</a></li>
                    <li><a href="users.php">Пользователи</a></li>
                    <li><a href="feedback.php">Обратная связь</a></li>
                    <li><a href="../index.php">Вернуться на сайт</a></li>
                    <li><a href="../logout.php">Выйти</a></li>
                </ul>
            </nav>
        </div>
        
        <div class="admin-content">
            <div class="admin-header">
                <h1>Детали заказа #<?php echo $order_id; ?></h1>
                <div class="admin-user">
                    <span>Вы вошли как: <?php echo $_SESSION['username']; ?></span>
                </div>
            </div>
            
            <?php
            // Вывод сообщений об ошибках или успехе
            if (isset($_SESSION['error_message'])) {
                echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
                unset($_SESSION['error_message']);
            }
            
            if (isset($_SESSION['success_message'])) {
                echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
                unset($_SESSION['success_message']);
            }
            ?>
            
            <div class="order-details-container">
                <div class="left-column">
                    <!-- Информация о заказе -->
                    <div class="order-summary">
                        <h3>Информация о заказе</h3>
                        <div class="order-info-grid">
                            <div class="info-group">
                                <div class="info-label">Дата создания:</div>
                                <div class="info-value"><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Статус:</div>
                                <div class="info-value">
                                    <?php
                                    $status_class = 'status-' . $order['status'];
                                    $status_text = $statuses[$order['status']] ?? $order['status'];
                                    ?>
                                    <span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                </div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Клиент:</div>
                                <div class="info-value">
                                    <?php if ($order['user_id']): ?>
                                        <?php echo htmlspecialchars($order['full_name'] ? $order['full_name'] : $order['username']); ?>
                                    <?php else: ?>
                                        Гость
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Email:</div>
                                <div class="info-value"><?php echo htmlspecialchars($order['email'] ?? 'Не указан'); ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Телефон:</div>
                                <div class="info-value"><?php echo htmlspecialchars($order['phone'] ?? 'Не указан'); ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Метод оплаты:</div>
                                <div class="info-value"><?php echo htmlspecialchars($order['payment_method'] ?? 'Не указан'); ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Адрес доставки:</div>
                                <div class="info-value"><?php echo htmlspecialchars($order['shipping_address'] ?? 'Не указан'); ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Номер отслеживания:</div>
                                <div class="info-value"><?php echo htmlspecialchars($order['tracking_number'] ?? 'Не указан'); ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Обновлено:</div>
                                <div class="info-value"><?php echo date('d.m.Y H:i', strtotime($order['updated_at'])); ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Итого:</div>
                                <div class="info-value"><?php echo format_price($order['total_amount']); ?> BYN</div>
                            </div>
                        </div>
                        
                        <?php if (!empty($order['comment'])): ?>
                            <div class="info-group" style="grid-column: 1 / span 2;">
                                <div class="info-label">Комментарий к заказу:</div>
                                <div class="info-value"><?php echo nl2br(htmlspecialchars($order['comment'])); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Товары заказа -->
                    <div class="order-items">
                        <h3>Товары в заказе</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 60px;">Фото</th>
                                    <th>Товар</th>
                                    <th>Цена</th>
                                    <th>Кол-во</th>
                                    <th>Сумма</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                    <tr>
                                        <td>
                                            <?php if ($item['image']): ?>
                                                <img src="<?php echo '../' . $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="product-image">
                                            <?php else: ?>
                                                <div class="no-image">Нет фото</div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="product-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                            <div class="product-id">ID: <?php echo $item['product_id']; ?></div>
                                        </td>
                                        <td>
                                            <?php echo format_price($item['price']); ?> BYN
                                            <?php if ($item['current_price'] != $item['price']): ?>
                                                <div class="price-change">
                                                    Текущая: <?php echo format_price($item['current_price']); ?> BYN
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><?php echo format_price($item['price'] * $item['quantity']); ?> BYN</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" style="text-align: right;">Итого:</td>
                                    <td><?php echo format_price($order['total_amount']); ?> BYN</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                
                <div class="right-column">
                    <!-- Действия с заказом -->
                    <div class="order-actions">
                        <h3>Управление заказом</h3>
                        <div style="margin-bottom: 20px;">
                            <a href="javascript:window.print();" class="button print-button">Печать заказа</a>
                            <a href="orders.php" class="button">Вернуться к списку</a>
                        </div>
                        
                        <form method="post" action="">
                            <div class="form-group">
                                <label for="status">Статус заказа</label>
                                <select name="status" id="status" class="form-control">
                                    <?php foreach ($statuses as $key => $value): ?>
                                        <option value="<?php echo $key; ?>" <?php echo $order['status'] === $key ? 'selected' : ''; ?>><?php echo $value; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="payment_method">Метод оплаты</label>
                                <select name="payment_method" id="payment_method" class="form-control">
                                    <option value="cash" <?php echo $order['payment_method'] === 'cash' ? 'selected' : ''; ?>>Наличными при получении</option>
                                    <option value="card" <?php echo $order['payment_method'] === 'card' ? 'selected' : ''; ?>>Банковской картой</option>
                                    <option value="bank_transfer" <?php echo $order['payment_method'] === 'bank_transfer' ? 'selected' : ''; ?>>Банковский перевод</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="shipping_address">Адрес доставки</label>
                                <textarea name="shipping_address" id="shipping_address" rows="3" class="form-control"><?php echo htmlspecialchars($order['shipping_address'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="tracking_number">Номер отслеживания</label>
                                <input type="text" name="tracking_number" id="tracking_number" value="<?php echo htmlspecialchars($order['tracking_number'] ?? ''); ?>" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label for="comment">Комментарий</label>
                                <textarea name="comment" id="comment" rows="3" class="form-control"><?php echo htmlspecialchars($order['comment'] ?? ''); ?></textarea>
                            </div>
                            
                            <button type="submit" name="update_order" class="button">Сохранить изменения</button>
                        </form>
                    </div>
                    
                    <!-- История изменений заказа -->
                    <div class="order-history">
                        <h3>История заказа</h3>
                        <?php if (empty($order_history)): ?>
                            <p>Нет записей об изменениях заказа</p>
                        <?php else: ?>
                            <?php foreach ($order_history as $history): ?>
                                <div class="history-item">
                                    <div class="history-date"><?php echo date('d.m.Y H:i', strtotime($history['created_at'])); ?></div>
                                    <div><?php echo htmlspecialchars($history['description']); ?></div>
                                    <div class="history-admin">
                                        <?php if ($history['admin_id']): ?>
                                            <small>Изменено: <?php echo htmlspecialchars($history['admin_username'] ?? 'Администратор #' . $history['admin_id']); ?></small>
                                        <?php else: ?>
                                            <small>Система</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Предпросмотр печати при нажатии на кнопку
        document.addEventListener('DOMContentLoaded', function() {
            const printButton = document.querySelector('.print-button');
            if (printButton) {
                printButton.addEventListener('click', function() {
                    window.print();
                });
            }
        });
    </script>
</body>
</html> 