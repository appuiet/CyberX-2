<?php
$page_title = 'Дашборд';
require_once '../config/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Проверяем права доступа
require_admin();

// Получение статистики
// 1. Общее количество продаж по месяцам
$stmt = $pdo->query("SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month, 
                    COUNT(*) as orders_count, 
                    SUM(total_amount) as revenue
                  FROM orders 
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                  GROUP BY month 
                  ORDER BY month");
$monthly_sales = $stmt->fetchAll();

// 2. Статистика по статусам заказов
$stmt = $pdo->query("SELECT 
                    status, 
                    COUNT(*) as count
                  FROM orders 
                  GROUP BY status");
$order_statuses = $stmt->fetchAll();

// 3. Топ-5 товаров по продажам
$stmt = $pdo->query("SELECT 
                    p.id, 
                    p.name, 
                    SUM(oi.quantity) as quantity_sold,
                    SUM(oi.quantity * oi.price) as total_revenue
                  FROM order_items oi
                  JOIN products p ON oi.product_id = p.id
                  JOIN orders o ON oi.order_id = o.id
                  WHERE o.status != 'cancelled'
                  GROUP BY p.id
                  ORDER BY quantity_sold DESC
                  LIMIT 5");
$top_products = $stmt->fetchAll();

// 4. Общая выручка и количество заказов
$stmt = $pdo->query("SELECT 
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_revenue
                  FROM orders
                  WHERE status != 'cancelled'");
$totals = $stmt->fetch();

// 5. Статистика по новым пользователям
$stmt = $pdo->query("SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month, 
                    COUNT(*) as new_users
                  FROM users 
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                  GROUP BY month 
                  ORDER BY month");
$new_users = $stmt->fetchAll();

// 6. Конверсия заказов (соотношение просмотров и заказов)
$stmt = $pdo->query("SELECT 
                    COUNT(*) as visits_count
                  FROM page_visits
                  WHERE page = 'product' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$product_views = $stmt->fetch();

$stmt = $pdo->query("SELECT 
                    COUNT(*) as orders_count
                  FROM orders
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$orders_30days = $stmt->fetch();

$conversion_rate = 0;
if ($product_views && $product_views['visits_count'] > 0) {
    $conversion_rate = ($orders_30days['orders_count'] / $product_views['visits_count']) * 100;
}

// Подготовка данных для графиков
$months_labels = [];
$monthly_orders = [];
$monthly_revenue = [];

foreach ($monthly_sales as $data) {
    $date = new DateTime($data['month'] . '-01');
    $months_labels[] = $date->format('M Y');
    $monthly_orders[] = $data['orders_count'];
    $monthly_revenue[] = $data['revenue'];
}

// Статусы заказов для круговой диаграммы
$status_labels = [];
$status_data = [];
$status_colors = [
    'pending' => '#ffc107',
    'processing' => '#17a2b8',
    'shipping' => '#6f42c1',
    'delivered' => '#28a745',
    'completed' => '#28a745',
    'cancelled' => '#dc3545',
    'refunded' => '#fd7e14'
];

foreach ($order_statuses as $status) {
    switch ($status['status']) {
        case 'pending':
            $status_labels[] = 'В обработке';
            break;
        case 'processing':
            $status_labels[] = 'Обрабатывается';
            break;
        case 'shipping':
            $status_labels[] = 'Отправлен';
            break;
        case 'delivered':
            $status_labels[] = 'Доставлен';
            break;
        case 'completed':
            $status_labels[] = 'Выполнен';
            break;
        case 'cancelled':
            $status_labels[] = 'Отменен';
            break;
        case 'refunded':
            $status_labels[] = 'Возвращен';
            break;
        default:
            $status_labels[] = $status['status'];
    }
    $status_data[] = $status['count'];
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $page_title; ?> - CYBERX</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .card {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            position: relative;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .card-title {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .card-value {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .card-subtitle {
            font-size: 14px;
            color: #6c757d;
        }
        
        .chart-container {
            height: 300px;
            position: relative;
        }
        
        .data-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }
        
        .data-item {
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            background-color: #f8f9fa;
        }
        
        .data-value {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .data-label {
            font-size: 14px;
            color: #6c757d;
        }
        
        .trend-up {
            color: #28a745;
        }
        
        .trend-down {
            color: #dc3545;
        }
        
        .product-list {
            list-style-type: none;
            padding: 0;
        }
        
        .product-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .product-name {
            font-weight: bold;
        }
        
        .product-sales {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }
        
        .product-quantity {
            font-weight: bold;
        }
        
        .product-revenue {
            font-size: 14px;
            color: #6c757d;
        }
        
        .gauge-container {
            width: 200px;
            height: 100px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
        }
        
        .gauge {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: conic-gradient(
                #dc3545 0% 33%,
                #ffc107 33% 66%,
                #28a745 66% 100%
            );
            position: relative;
        }
        
        .gauge::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 50%;
        }
        
        .gauge-value {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 24px;
            font-weight: bold;
        }
        
        @media screen and (max-width: 768px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
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
                    <li><a href="dashboard.php" class="active">Дашборд</a></li>
                    <li><a href="products.php">Товары</a></li>
                    <li><a href="categories.php">Категории</a></li>
                    <li><a href="orders.php">Заказы</a></li>
                    <li><a href="users.php">Пользователи</a></li>
                    <li><a href="feedback.php">Обратная связь</a></li>
                    <li><a href="../index.php">Вернуться на сайт</a></li>
                    <li><a href="../logout.php">Выйти</a></li>
                </ul>
            </nav>
        </div>
        
        <div class="admin-content">
            <div class="admin-header">
                <h1>Аналитический дашборд</h1>
                <div class="admin-user">
                    <span>Вы вошли как: <?php echo $_SESSION['username']; ?></span>
                </div>
            </div>
            
            <!-- Ключевые показатели -->
            <div class="dashboard-container">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Общая выручка</h3>
                    </div>
                    <div class="card-value"><?php echo format_price($totals['total_revenue'] ?? 0); ?> BYN</div>
                    <div class="card-subtitle">За все время</div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Всего заказов</h3>
                    </div>
                    <div class="card-value"><?php echo $totals['total_orders'] ?? 0; ?></div>
                    <div class="card-subtitle">За все время</div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Конверсия</h3>
                    </div>
                    <div class="card-value"><?php echo number_format($conversion_rate, 2); ?>%</div>
                    <div class="card-subtitle">За последние 30 дней</div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Средний чек</h3>
                    </div>
                    <div class="card-value">
                        <?php 
                        $avg_order = 0;
                        if ($totals['total_orders'] > 0) {
                            $avg_order = $totals['total_revenue'] / $totals['total_orders'];
                        }
                        echo format_price($avg_order); 
                        ?> BYN
                    </div>
                    <div class="card-subtitle">За все время</div>
                </div>
            </div>
            
            <!-- Графики -->
            <div class="dashboard-container">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Продажи по месяцам</h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Статусы заказов</h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="ordersStatusChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-container">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Топ товаров по продажам</h3>
                    </div>
                    <ul class="product-list">
                        <?php foreach ($top_products as $product): ?>
                            <li class="product-item">
                                <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                <div class="product-sales">
                                    <div class="product-quantity"><?php echo $product['quantity_sold']; ?> шт.</div>
                                    <div class="product-revenue"><?php echo format_price($product['total_revenue']); ?> BYN</div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Новые пользователи</h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="newUsersChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // График продаж по месяцам
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($months_labels); ?>,
                datasets: [
                    {
                        label: 'Количество заказов',
                        data: <?php echo json_encode($monthly_orders); ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Выручка (BYN)',
                        data: <?php echo json_encode($monthly_revenue); ?>,
                        backgroundColor: 'rgba(255, 99, 132, 0.5)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1,
                        type: 'line',
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Количество заказов'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                        title: {
                            display: true,
                            text: 'Выручка (BYN)'
                        }
                    }
                }
            }
        });
        
        // Круговая диаграмма статусов заказов
        const statusCtx = document.getElementById('ordersStatusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($status_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($status_data); ?>,
                    backgroundColor: [
                        '#ffc107', // В обработке
                        '#17a2b8', // Обрабатывается
                        '#6f42c1', // Отправлен
                        '#28a745', // Доставлен/Выполнен
                        '#28a745', // Повтор для выполненных
                        '#dc3545', // Отменен
                        '#fd7e14'  // Возвращен
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                }
            }
        });
        
        // График новых пользователей
        const usersCtx = document.getElementById('newUsersChart').getContext('2d');
        const usersData = {
            labels: <?php echo json_encode($months_labels); ?>,
            datasets: [{
                label: 'Новые пользователи',
                data: <?php 
                    $users_data = [];
                    foreach ($new_users as $data) {
                        $users_data[] = $data['new_users'];
                    }
                    echo json_encode($users_data); 
                ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        };
        
        const usersChart = new Chart(usersCtx, {
            type: 'line',
            data: usersData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html> 