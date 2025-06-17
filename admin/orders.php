<?php
// Заголовок страницы
$page_title = "Управление заказами";
include 'admin_header.php';

// Обработка изменения статуса заказа
if (isset($_POST['update_status'])) {
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = :status WHERE id = :order_id");
        $stmt->bindParam(':status', $_POST['status']);
        $stmt->bindParam(':order_id', $_POST['order_id']);
        $stmt->execute();
        $success_message = "Статус заказа успешно обновлен.";
    } catch (PDOException $e) {
        $error_message = "Ошибка при обновлении статуса заказа: " . $e->getMessage();
    }
}

// Получение списка заказов
try {
    $stmt = $pdo->query("SELECT o.*, u.username, u.email FROM orders o 
                        LEFT JOIN users u ON o.user_id = u.id 
                        ORDER BY o.created_at DESC");
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Ошибка при получении заказов: " . $e->getMessage();
    $orders = [];
}

// Получение деталей заказа
$order_details = [];
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    try {
        // Получаем информацию о заказе
        $stmt = $pdo->prepare("SELECT o.*, u.username, u.email, u.full_name, u.phone 
                              FROM orders o 
                              LEFT JOIN users u ON o.user_id = u.id 
                              WHERE o.id = :order_id");
        $stmt->bindParam(':order_id', $_GET['view']);
        $stmt->execute();
        $order = $stmt->fetch();
        
        if ($order) {
            // Получаем товары в заказе
            $stmt = $pdo->prepare("SELECT oi.*, p.name, p.image FROM order_items oi 
                                  JOIN products p ON oi.product_id = p.id 
                                  WHERE oi.order_id = :order_id");
            $stmt->bindParam(':order_id', $_GET['view']);
            $stmt->execute();
            $order_items = $stmt->fetchAll();
            
            $order_details = [
                'order' => $order,
                'items' => $order_items
            ];
        }
    } catch (PDOException $e) {
        $error_message = "Ошибка при получении деталей заказа: " . $e->getMessage();
    }
}
?>

<?php if (isset($_GET['view']) && !empty($order_details)): ?>
    <!-- Детали заказа -->
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between">
                    <h5>Детали заказа #<?= $order_details['order']['id'] ?></h5>
                    <a href="orders.php" class="btn btn-sm btn-secondary">Вернуться к списку</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Информация о заказе</h6>
                            <table class="table">
                                <tr>
                                    <th>ID заказа:</th>
                                    <td><?= $order_details['order']['id'] ?></td>
                                </tr>
                                <tr>
                                    <th>Дата заказа:</th>
                                    <td><?= date('d.m.Y H:i', strtotime($order_details['order']['created_at'])) ?></td>
                                </tr>
                                <tr>
                                    <th>Статус:</th>
                                    <td>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="order_id" value="<?= $order_details['order']['id'] ?>">
                                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="pending" <?= $order_details['order']['status'] == 'pending' ? 'selected' : '' ?>>В обработке</option>
                                                <option value="processing" <?= $order_details['order']['status'] == 'processing' ? 'selected' : '' ?>>Обрабатывается</option>
                                                <option value="completed" <?= $order_details['order']['status'] == 'completed' ? 'selected' : '' ?>>Выполнен</option>
                                                <option value="cancelled" <?= $order_details['order']['status'] == 'cancelled' ? 'selected' : '' ?>>Отменен</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Общая сумма:</th>
                                    <td><?= format_price($order_details['order']['total_amount']) ?> BYN</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Информация о клиенте</h6>
                            <table class="table">
                                <tr>
                                    <th>Пользователь:</th>
                                    <td><?= $order_details['order']['username'] ?? 'Гость' ?></td>
                                </tr>
                                <tr>
                                    <th>Полное имя:</th>
                                    <td><?= htmlspecialchars($order_details['order']['full_name'] ?? 'Не указано') ?></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td><?= htmlspecialchars($order_details['order']['email'] ?? 'Не указано') ?></td>
                                </tr>
                                <tr>
                                    <th>Телефон:</th>
                                    <td><?= htmlspecialchars($order_details['order']['phone'] ?? 'Не указано') ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <h6 class="mt-3">Товары в заказе</h6>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Изображение</th>
                                    <th>Название</th>
                                    <th>Цена</th>
                                    <th>Количество</th>
                                    <th>Итого</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_details['items'] as $item): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($item['image'])): ?>
                                                <img src="../<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="max-width: 50px; max-height: 50px;">
                                            <?php else: ?>
                                                <span class="text-muted">Нет фото</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($item['name']) ?></td>
                                        <td><?= format_price($item['price']) ?> BYN</td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td><?= format_price($item['price'] * $item['quantity']) ?> BYN</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">Итого:</th>
                                    <th><?= format_price($order_details['order']['total_amount']) ?> BYN</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Список заказов -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Список заказов</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($orders)): ?>
                        <p>Заказы не найдены.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Пользователь</th>
                                        <th>Email</th>
                                        <th>Дата</th>
                                        <th>Статус</th>
                                        <th>Сумма</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><?= $order['id'] ?></td>
                                            <td><?= htmlspecialchars($order['username'] ?? 'Гость') ?></td>
                                            <td><?= htmlspecialchars($order['email'] ?? 'Нет данных') ?></td>
                                            <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                                            <td>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                        <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>В обработке</option>
                                                        <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>Обрабатывается</option>
                                                        <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>>Выполнен</option>
                                                        <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Отменен</option>
                                                    </select>
                                                    <input type="hidden" name="update_status" value="1">
                                                </form>
                                            </td>
                                            <td><?= format_price($order['total_amount']) ?> BYN</td>
                                            <td>
                                                <a href="orders.php?view=<?= $order['id'] ?>" class="btn btn-sm btn-primary">Просмотр</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include 'admin_footer.php'; ?>
