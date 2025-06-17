<?php
// Запускаем сессию
session_start();

// Подключаем необходимые файлы
require_once 'config/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/cart.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тестирование корзины - CYBERX</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Sofia+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="diagnostic-styles.css">
</head>
<body>
    <div class="diagnostic-container">
        <h1>Тестирование корзины</h1>

        <!-- Текущее состояние сессии -->
        <h2>Текущее состояние сессии</h2>
        <pre>
<?php print_r($_SESSION); ?>
        </pre>

        <?php
        // Тестовые товары
        $test_products = [
            [
                'id' => 1,
                'name' => 'Тестовый товар 1',
                'price' => 100.00
            ],
            [
                'id' => 2,
                'name' => 'Тестовый товар 2',
                'price' => 200.00
            ],
            [
                'id' => 3,
                'name' => 'Тестовый товар 3',
                'price' => 300.00
            ]
        ];

        // Обработка действий с корзиной
        if (isset($_GET['action'])) {
            $action = $_GET['action'];
            
            if ($action === 'add') {
                $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
                $quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;
                $price = 0;
                
                // Находим цену товара
                foreach ($test_products as $product) {
                    if ($product['id'] == $product_id) {
                        $price = $product['price'];
                        break;
                    }
                }
                
                add_to_cart($product_id, $price, $quantity);
                echo "<div class='success-box'><p>Товар добавлен в корзину!</p></div>";
            } elseif ($action === 'update') {
                $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
                $quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;
                
                update_cart_item($product_id, $quantity);
                echo "<div class='success-box'><p>Количество товара обновлено!</p></div>";
            } elseif ($action === 'remove') {
                $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
                
                remove_from_cart($product_id);
                echo "<div class='success-box'><p>Товар удален из корзины!</p></div>";
            } elseif ($action === 'clear') {
                clear_cart();
                echo "<div class='success-box'><p>Корзина очищена!</p></div>";
            }
        }
        ?>

        <!-- Информация о корзине -->
        <h2>Информация о корзине</h2>
        <div class="info-box">
            <p>Количество товаров в корзине: <strong><?php echo get_cart_count(); ?></strong></p>
            <p>Общая стоимость: <strong><?php echo format_price(get_cart_total()); ?> руб.</strong></p>
        </div>

        <!-- Товары в корзине -->
        <?php
        $cart_items = get_cart_items();
        if (!empty($cart_items)) {
            echo "<h2>Товары в корзине</h2>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Количество</th><th>Цена</th><th>Стоимость</th><th>Действия</th></tr>";
            
            foreach ($cart_items as $product_id => $item) {
                echo "<tr>";
                echo "<td>{$product_id}</td>";
                echo "<td>{$item['quantity']}</td>";
                echo "<td>" . format_price($item['price']) . " руб.</td>";
                echo "<td>" . format_price($item['price'] * $item['quantity']) . " руб.</td>";
                echo "<td>
                    <a href='test_cart.php?action=update&id={$product_id}&quantity=" . ($item['quantity'] + 1) . "' class='btn btn-outline'>+</a>
                    <a href='test_cart.php?action=update&id={$product_id}&quantity=" . ($item['quantity'] - 1) . "' class='btn btn-outline'>-</a>
                    <a href='test_cart.php?action=remove&id={$product_id}' class='btn'>Удалить</a>
                </td>";
                echo "</tr>";
            }
            
            echo "</table>";
            echo "<p><a href='test_cart.php?action=clear' class='btn'>Очистить корзину</a></p>";
        } else {
            echo "<div class='info-box'><p>Корзина пуста.</p></div>";
        }
        ?>

        <!-- Доступные товары -->
        <h2>Доступные товары</h2>
        <table>
            <tr><th>ID</th><th>Название</th><th>Цена</th><th>Действия</th></tr>
            <?php foreach ($test_products as $product): ?>
            <tr>
                <td><?php echo $product['id']; ?></td>
                <td><?php echo $product['name']; ?></td>
                <td><?php echo format_price($product['price']); ?> руб.</td>
                <td>
                    <a href='test_cart.php?action=add&id=<?php echo $product['id']; ?>&quantity=1' class='btn'>Добавить в корзину</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <!-- Ссылки для тестирования -->
        <div class="links-section">
            <h2>Тестовые ссылки</h2>
            <ul>
                <li><a href="test_cart.php" class="btn">Обновить страницу</a></li>
                <li><a href="check_database.php" class="btn btn-outline">Проверка базы данных</a></li>
                <li><a href="check_passwords.php" class="btn btn-outline">Проверка паролей</a></li>
                <li><a href="fix_sessions.php" class="btn btn-outline">Проверка сессий</a></li>
                <li><a href="session_fix_instructions.php" class="btn btn-outline">Инструкции по исправлению проблем</a></li>
                <li><a href="login.php" class="btn">Страница входа</a></li>
                <li><a href="index.php" class="btn">Главная страница</a></li>
            </ul>
        </div>
    </div>
</body>
</html> 