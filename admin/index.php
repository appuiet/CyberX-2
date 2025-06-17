<?php
$page_title = 'Панель администратора';
include 'admin_header.php';
?>
            
            <div class="admin-dashboard">
                <div class="dashboard-item">
                    <h3>Статистика</h3>
                    <?php
                    // Получаем статистику
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
                    $products_count = $stmt->fetch()['count'];
                    
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
                    $users_count = $stmt->fetch()['count'];
                    
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
                    $orders_count = $stmt->fetch()['count'];
                    
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM feedback");
                    $feedback_count = $stmt->fetch()['count'];
                    ?>
                    <div class="stats">
                        <div class="stat-item">
                            <span class="stat-value"><?php echo $products_count; ?></span>
                            <span class="stat-label">Товаров</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value"><?php echo $users_count; ?></span>
                            <span class="stat-label">Пользователей</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value"><?php echo $orders_count; ?></span>
                            <span class="stat-label">Заказов</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value"><?php echo $feedback_count; ?></span>
                            <span class="stat-label">Сообщений</span>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-item">
                    <h3>Последние заказы</h3>
                    <?php
                    // Получаем последние заказы
                    $stmt = $pdo->query("SELECT o.*, u.username FROM orders o 
                                        LEFT JOIN users u ON o.user_id = u.id 
                                        ORDER BY o.created_at DESC LIMIT 5");
                    $latest_orders = $stmt->fetchAll();
                    
                    if (empty($latest_orders)) {
                        echo '<p>Нет заказов</p>';
                    } else {
                    ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>№</th>
                                <th>Пользователь</th>
                                <th>Дата</th>
                                <th>Статус</th>
                                <th>Сумма</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($latest_orders as $order): ?>
                            <tr>
                                <td><?php echo $order['id']; ?></td>
                                <td><?php echo $order['username'] ?? 'Гость'; ?></td>
                                <td><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></td>
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
                                <td><?php echo format_price($order['total_amount']); ?> BYN</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php } ?>
                </div>
                
                <div class="dashboard-item">
                    <h3>Последние сообщения обратной связи</h3>
                    <?php
                    // Получаем последние сообщения обратной связи
                    $stmt = $pdo->query("SELECT f.*, u.username FROM feedback f 
                                        LEFT JOIN users u ON f.user_id = u.id 
                                        ORDER BY f.created_at DESC LIMIT 5");
                    $latest_feedback = $stmt->fetchAll();
                    
                    if (empty($latest_feedback)) {
                        echo '<p>Нет сообщений</p>';
                    } else {
                    ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>№</th>
                                <th>Имя</th>
                                <th>Email</th>
                                <th>Тема</th>
                                <th>Дата</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($latest_feedback as $feedback): ?>
                            <tr>
                                <td><?php echo $feedback['id']; ?></td>
                                <td><?php echo $feedback['name']; ?></td>
                                <td><?php echo $feedback['email']; ?></td>
                                <td><?php echo $feedback['subject']; ?></td>
                                <td><?php echo date('d.m.Y H:i', strtotime($feedback['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php } ?>
                </div>
            </div>
<?php include 'admin_footer.php'; ?> 