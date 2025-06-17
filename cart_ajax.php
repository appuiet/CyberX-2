<?php
// Запускаем сессию перед любыми операциями
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/cart.php';

// Устанавливаем заголовок для JSON-ответа
header('Content-Type: application/json');

// Отключаем отображение ошибок для предотвращения порчи JSON
ini_set('display_errors', 0);
error_reporting(0);

// Создаем специальный лог-файл для отладки cart_ajax.php
$log_file = __DIR__ . '/cart_ajax_debug.log';
file_put_contents($log_file, "=== Запрос к cart_ajax.php: " . date('Y-m-d H:i:s') . " ===\n", FILE_APPEND);
file_put_contents($log_file, "POST данные: " . print_r($_POST, true) . "\n", FILE_APPEND);
file_put_contents($log_file, "GET данные: " . print_r($_GET, true) . "\n", FILE_APPEND);
file_put_contents($log_file, "SESSION данные: " . print_r($_SESSION, true) . "\n", FILE_APPEND);

// Проверяем, передано ли действие
if (!isset($_POST['action'])) {
    $error_response = ['success' => false, 'message' => 'Неверный запрос: отсутствует параметр action'];
    file_put_contents($log_file, "Ошибка: " . json_encode($error_response) . "\n", FILE_APPEND);
    echo json_encode($error_response);
    exit;
}

$action = $_POST['action'];
$response = ['success' => false];

file_put_contents($log_file, "Действие: " . $action . "\n", FILE_APPEND);

switch ($action) {
    case 'add':
        // Добавление товара в корзину
        if (!isset($_POST['product_id']) || !isset($_POST['price'])) {
            $error_response = ['success' => false, 'message' => 'Неверные параметры: отсутствует product_id или price'];
            file_put_contents($log_file, "Ошибка: " . json_encode($error_response) . "\n", FILE_APPEND);
            echo json_encode($error_response);
            exit;
        }
        
        $product_id = (int)$_POST['product_id'];
        $price = (float)$_POST['price'];
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        
        file_put_contents($log_file, "Параметры: product_id={$product_id}, price={$price}, quantity={$quantity}\n", FILE_APPEND);
        
        // Проверяем, существует ли товар
        try {
            $stmt = $pdo->prepare("SELECT id, name, image FROM products WHERE id = :product_id");
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->execute();
            $product = $stmt->fetch();
            
            if (!$product) {
                $error_response = ['success' => false, 'message' => 'Товар не найден в базе данных'];
                file_put_contents($log_file, "Ошибка: " . json_encode($error_response) . "\n", FILE_APPEND);
                echo json_encode($error_response);
                exit;
            }
            
            file_put_contents($log_file, "Товар найден: " . print_r($product, true) . "\n", FILE_APPEND);
            
            // Добавляем товар в корзину
            try {
                $result = add_to_cart($product_id, $price, $quantity);
                
                file_put_contents($log_file, "Результат add_to_cart: " . ($result ? 'успешно' : 'ошибка') . "\n", FILE_APPEND);
                
                if (!$result) {
                    $error_response = ['success' => false, 'message' => 'Ошибка при добавлении товара в корзину'];
                    file_put_contents($log_file, "Ошибка: " . json_encode($error_response) . "\n", FILE_APPEND);
                    echo json_encode($error_response);
                    exit;
                }
                
                // Получаем обновленные данные корзины
                $cart_count = get_cart_count();
                $cart_total = get_cart_total();
                
                $response = [
                    'success' => true,
                    'message' => 'Товар добавлен в корзину',
                    'product_name' => $product['name'],
                    'product_image' => $product['image'],
                    'cart_count' => $cart_count,
                    'cart_total' => format_price($cart_total) . ' BYN'
                ];
                
                file_put_contents($log_file, "Успешный ответ: " . json_encode($response) . "\n", FILE_APPEND);
            } catch (Exception $e) {
                $error_response = ['success' => false, 'message' => 'Исключение при добавлении товара: ' . $e->getMessage()];
                file_put_contents($log_file, "Исключение: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
                echo json_encode($error_response);
                exit;
            }
        } catch (Exception $e) {
            $error_response = ['success' => false, 'message' => 'Ошибка БД при проверке товара: ' . $e->getMessage()];
            file_put_contents($log_file, "Исключение БД: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
            echo json_encode($error_response);
            exit;
        }
        break;
        
    case 'update':
        // Обновление количества товара в корзине
        if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
            $error_response = ['success' => false, 'message' => 'Неверные параметры: отсутствует product_id или quantity'];
            file_put_contents($log_file, "Ошибка: " . json_encode($error_response) . "\n", FILE_APPEND);
            echo json_encode($error_response);
            exit;
        }
        
        $product_id = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];
        
        file_put_contents($log_file, "Параметры update: product_id={$product_id}, quantity={$quantity}\n", FILE_APPEND);
        
        try {
            // Обновляем количество товара
            update_cart_item($product_id, $quantity);
            
            // Получаем обновленные данные корзины
            $cart_count = get_cart_count();
            $cart_total = get_cart_total();
            
            // Получаем актуальные данные о товаре
            $stmt = $pdo->prepare("SELECT p.name, p.price, ci.quantity, ci.price as cart_price 
                                  FROM products p 
                                  LEFT JOIN cart_items ci ON p.id = ci.product_id AND ci.cart_id = 
                                    (SELECT id FROM cart WHERE user_id = :user_id)
                                  WHERE p.id = :product_id");
            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->execute();
            $product = $stmt->fetch();
            
            // Если товар найден в БД, используем его информацию
            $price = 0;
            $product_name = "";
            $subtotal = 0;
            
            if ($product) {
                $price = $product['cart_price'] ? $product['cart_price'] : $product['price'];
                $product_name = $product['name'];
                $subtotal = $price * $quantity;
            } else {
                // Если товар не найден в БД, ищем в сессии
                if (isset($_SESSION['cart'][$product_id])) {
                    $price = $_SESSION['cart'][$product_id]['price'];
                    $subtotal = $price * $quantity;
                }
            }
            
            file_put_contents($log_file, "Товар: " . print_r($product, true) . "\n", FILE_APPEND);
            file_put_contents($log_file, "Цена: {$price}, Количество: {$quantity}, Подытог: {$subtotal}\n", FILE_APPEND);
            
            $response = [
                'success' => true,
                'message' => 'Корзина обновлена',
                'cart_count' => $cart_count,
                'cart_total' => format_price($cart_total) . ' BYN',
                'product_price' => $price,
                'product_quantity' => $quantity,
                'product_subtotal' => format_price($subtotal) . ' BYN',
                'product_subtotal_raw' => $subtotal
            ];
            
            file_put_contents($log_file, "Успешное обновление корзины: " . json_encode($response) . "\n", FILE_APPEND);
        } catch (Exception $e) {
            $error_response = ['success' => false, 'message' => 'Ошибка при обновлении: ' . $e->getMessage()];
            file_put_contents($log_file, "Исключение при обновлении: " . $e->getMessage() . "\n", FILE_APPEND);
            echo json_encode($error_response);
            exit;
        }
        break;
        
    case 'remove':
        // Удаление товара из корзины
        if (!isset($_POST['product_id'])) {
            $error_response = ['success' => false, 'message' => 'Неверные параметры: отсутствует product_id'];
            file_put_contents($log_file, "Ошибка: " . json_encode($error_response) . "\n", FILE_APPEND);
            echo json_encode($error_response);
            exit;
        }
        
        $product_id = (int)$_POST['product_id'];
        
        file_put_contents($log_file, "Удаление товара: product_id={$product_id}\n", FILE_APPEND);
        
        try {
            // Удаляем товар из корзины
            remove_from_cart($product_id);
            
            // Получаем обновленные данные корзины
            $cart_count = get_cart_count();
            $cart_total = get_cart_total();
            
            $response = [
                'success' => true,
                'message' => 'Товар удален из корзины',
                'cart_count' => $cart_count,
                'cart_total' => format_price($cart_total) . ' BYN'
            ];
            
            file_put_contents($log_file, "Успешное удаление товара: " . json_encode($response) . "\n", FILE_APPEND);
        } catch (Exception $e) {
            $error_response = ['success' => false, 'message' => 'Ошибка при удалении: ' . $e->getMessage()];
            file_put_contents($log_file, "Исключение при удалении: " . $e->getMessage() . "\n", FILE_APPEND);
            echo json_encode($error_response);
            exit;
        }
        break;
        
    case 'clear':
        // Очистка корзины
        file_put_contents($log_file, "Очистка корзины\n", FILE_APPEND);
        
        try {
            clear_cart();
            
            $response = [
                'success' => true,
                'message' => 'Корзина очищена',
                'cart_count' => 0,
                'cart_total' => '0.00 BYN'
            ];
            
            file_put_contents($log_file, "Успешная очистка корзины\n", FILE_APPEND);
        } catch (Exception $e) {
            $error_response = ['success' => false, 'message' => 'Ошибка при очистке: ' . $e->getMessage()];
            file_put_contents($log_file, "Исключение при очистке: " . $e->getMessage() . "\n", FILE_APPEND);
            echo json_encode($error_response);
            exit;
        }
        break;
        
    case 'get_count':
        // Получение количества товаров в корзине
        file_put_contents($log_file, "Запрос количества товаров\n", FILE_APPEND);
        
        try {
            $cart_count = get_cart_count();
            
            $response = [
                'success' => true,
                'cart_count' => $cart_count
            ];
            
            file_put_contents($log_file, "Количество товаров: {$cart_count}\n", FILE_APPEND);
        } catch (Exception $e) {
            $error_response = ['success' => false, 'message' => 'Ошибка при получении количества: ' . $e->getMessage()];
            file_put_contents($log_file, "Исключение при получении количества: " . $e->getMessage() . "\n", FILE_APPEND);
            echo json_encode($error_response);
            exit;
        }
        break;
        
    case 'check_cart':
        // Проверка наличия товаров в корзине
        if (!isset($_POST['product_ids'])) {
            $error_response = ['success' => false, 'message' => 'Неверные параметры: отсутствует product_ids'];
            file_put_contents($log_file, "Ошибка: " . json_encode($error_response) . "\n", FILE_APPEND);
            echo json_encode($error_response);
            exit;
        }
        
        file_put_contents($log_file, "Проверка товаров в корзине\n", FILE_APPEND);
        
        try {
            $product_ids = json_decode($_POST['product_ids'], true);
            $cart_items = get_cart_items();
            $in_cart = [];
            
            foreach ($product_ids as $product_id) {
                $in_cart[$product_id] = isset($cart_items[$product_id]);
            }
            
            $response = [
                'success' => true,
                'in_cart' => $in_cart,
                'cart_count' => get_cart_count()
            ];
            
            file_put_contents($log_file, "Результаты проверки: " . json_encode($in_cart) . "\n", FILE_APPEND);
        } catch (Exception $e) {
            $error_response = ['success' => false, 'message' => 'Ошибка при проверке: ' . $e->getMessage()];
            file_put_contents($log_file, "Исключение при проверке: " . $e->getMessage() . "\n", FILE_APPEND);
            echo json_encode($error_response);
            exit;
        }
        break;
        
    default:
        $error_response = ['success' => false, 'message' => 'Неизвестное действие: ' . $action];
        file_put_contents($log_file, "Ошибка: " . json_encode($error_response) . "\n", FILE_APPEND);
        echo json_encode($error_response);
        exit;
}

file_put_contents($log_file, "=== Конец запроса ===\n\n", FILE_APPEND);

echo json_encode($response);
?> 