<?php
// Проверка авторизации администратора
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = 'Для доступа к админ-панели необходимо войти в систему с правами администратора';
    header('Location: ../index.php');
    exit;
}

// Получаем текущую страницу для подсветки активного пункта меню
$current_page = basename($_SERVER['PHP_SELF']);

// Подключение к базе данных и функции
require_once '../config/db_connect.php';
require_once '../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo isset($page_title) ? $page_title . ' - CYBERX' : 'Админ-панель - CYBERX'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../modern-styles.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --danger-color: #dc3545;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: 280px;
            background: linear-gradient(135deg, #212529, #343a40);
            color: #fff;
            position: fixed;
            height: 100vh;
            box-shadow: 3px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
            transition: all 0.3s;
        }
        
        .admin-logo {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .logo-image {
            max-width: 80px;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        
        .admin-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .admin-menu li {
            margin: 5px 0;
        }
        
        .admin-menu a {
            display: block;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 4px solid transparent;
            font-weight: 500;
        }
        
        .admin-menu a:hover {
            background-color: rgba(255,255,255,0.1);
            color: #fff;
            border-left-color: var(--primary-color);
        }
        
        .admin-menu a.active {
            background-color: rgba(13,110,253,0.2);
            color: #fff;
            border-left-color: var(--primary-color);
        }
        
        .admin-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .admin-content {
            flex: 1;
            margin-left: 280px;
            padding: 20px;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            margin-bottom: 30px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .admin-user {
            display: flex;
            align-items: center;
            background-color: #fff;
            padding: 8px 15px;
            border-radius: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .admin-user i {
            color: var(--primary-color);
            margin-right: 8px;
            font-size: 1.2em;
        }
        
        .alert {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid;
        }
        
        .alert-success {
            background-color: rgba(25,135,84,0.1);
            border-color: var(--success-color);
            color: var(--success-color);
        }
        
        .alert-danger {
            background-color: rgba(220,53,69,0.1);
            border-color: var(--danger-color);
            color: var(--danger-color);
        }
        
        /* Адаптивная верстка */
        @media (max-width: 992px) {
            .admin-sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .admin-logo h2, .admin-menu span {
                display: none;
            }
            
            .admin-content {
                margin-left: 70px;
            }
            
            .admin-menu a {
                text-align: center;
                padding: 15px 5px;
            }
            
            .admin-menu i {
                margin-right: 0;
                font-size: 1.2em;
            }
        }
        
        @media (max-width: 768px) {
            .admin-sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .admin-container {
                flex-direction: column;
            }
            
            .admin-content {
                margin-left: 0;
            }
            
            .admin-logo h2 {
                display: block;
            }
            
            .admin-menu span {
                display: inline;
            }
            
            .admin-menu a {
                text-align: left;
                padding: 12px 20px;
            }
            
            .admin-menu i {
                margin-right: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-sidebar">
            <div class="admin-logo">
                <a href="../index.php">
                    <img src="../IMG/logo.jpg" alt="Логотип" class="logo-image">
                </a>
                <h2>Панель администратора</h2>
            </div>
            <nav class="admin-menu">
                <ul>
                    <li><a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt"></i> <span>Главная</span>
                    </a></li>
                    <li><a href="products.php" class="<?php echo $current_page == 'products.php' ? 'active' : ''; ?>">
                        <i class="fas fa-shopping-cart"></i> <span>Товары</span>
                    </a></li>
                    <li><a href="categories.php" class="<?php echo $current_page == 'categories.php' ? 'active' : ''; ?>">
                        <i class="fas fa-folder"></i> <span>Категории</span>
                    </a></li>
                    <li><a href="orders.php" class="<?php echo $current_page == 'orders.php' ? 'active' : ''; ?>">
                        <i class="fas fa-clipboard-list"></i> <span>Заказы</span>
                    </a></li>
                    <li><a href="users.php" class="<?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i> <span>Пользователи</span>
                    </a></li>
                    <li><a href="feedback.php" class="<?php echo $current_page == 'feedback.php' ? 'active' : ''; ?>">
                        <i class="fas fa-comments"></i> <span>Обратная связь</span>
                    </a></li>
                    <li><a href="../index.php">
                        <i class="fas fa-home"></i> <span>Вернуться на сайт</span>
                    </a></li>
                    <li><a href="../logout.php" class="text-danger">
                        <i class="fas fa-sign-out-alt"></i> <span>Выйти</span>
                    </a></li>
                </ul>
            </nav>
        </div>
        
        <div class="admin-content">
            <div class="admin-header">
                <h1><?php echo isset($page_title) ? $page_title : 'Админ-панель'; ?></h1>
                <div class="admin-user">
                    <i class="fas fa-user-circle"></i>
                    <span>Вы вошли как: <strong><?php echo $_SESSION['username']; ?></strong></span>
                </div>
            </div>
            
            <?php
            // Вывод сообщений об ошибках или успехе
            if (isset($_SESSION['error_message'])) {
                echo '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>' . $_SESSION['error_message'] . '</div>';
                unset($_SESSION['error_message']);
            }
            
            if (isset($_SESSION['success_message'])) {
                echo '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>' . $_SESSION['success_message'] . '</div>';
                unset($_SESSION['success_message']);
            }
            
            if (isset($error_message)) {
                echo '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>' . $error_message . '</div>';
            }
            
            if (isset($success_message)) {
                echo '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>' . $success_message . '</div>';
            }
            ?>

