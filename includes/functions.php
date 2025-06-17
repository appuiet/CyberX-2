<?php
// Функция для очистки и валидации входных данных
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Функция для проверки, авторизован ли пользователь
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Функция для проверки, является ли пользователь администратором
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Функция для перенаправления
function redirect($url) {
    header("Location: $url");
    exit;
}

// Функция для вывода сообщений об ошибках
function display_error($message) {
    return "<div class='alert alert-danger'>$message</div>";
}

// Функция для вывода сообщений об успехе
function display_success($message) {
    return "<div class='alert alert-success'>$message</div>";
}

// Функция для получения всех категорий
function get_all_categories($pdo) {
    $stmt = $pdo->prepare("
        SELECT c.*, COUNT(p.id) as product_count 
        FROM categories c 
        LEFT JOIN products p ON c.id = p.category_id 
        GROUP BY c.id 
        ORDER BY c.name
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Функция для получения товаров по категории
function get_products_by_category($pdo, $category_id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = :category_id");
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Функция для получения информации о товаре по ID
function get_product_by_id($pdo, $product_id) {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p 
                          JOIN categories c ON p.category_id = c.id 
                          WHERE p.id = :product_id");
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

// Функция для получения категории по slug
function get_category_by_slug($pdo, $slug) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = :slug");
    $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch();
}

// Функция для форматирования цены
function format_price($price) {
    return number_format($price, 2, '.', ' ');
}

// Функция для создания заказа
function create_order($pdo, $user_id, $cart_items, $total_amount) {
    try {
        $pdo->beginTransaction();
        
        // Создаем заказ
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount) VALUES (:user_id, :total_amount)");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':total_amount', $total_amount, PDO::PARAM_STR);
        $stmt->execute();
        
        $order_id = $pdo->lastInsertId();
        
        // Добавляем товары в заказ
        foreach ($cart_items as $item) {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) 
                                  VALUES (:order_id, :product_id, :quantity, :price)");
            $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $item['product_id'], PDO::PARAM_INT);
            $stmt->bindParam(':quantity', $item['quantity'], PDO::PARAM_INT);
            $stmt->bindParam(':price', $item['price'], PDO::PARAM_STR);
            $stmt->execute();
            
            // Обновляем количество товара на складе
            $stmt = $pdo->prepare("UPDATE products SET stock = stock - :quantity WHERE id = :product_id");
            $stmt->bindParam(':quantity', $item['quantity'], PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $item['product_id'], PDO::PARAM_INT);
            $stmt->execute();
        }
        
        $pdo->commit();
        return $order_id;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

// Функция для получения заказов пользователя
function get_user_orders($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Функция для получения деталей заказа
function get_order_details($pdo, $order_id) {
    $stmt = $pdo->prepare("SELECT oi.*, p.name, p.image FROM order_items oi 
                          JOIN products p ON oi.product_id = p.id 
                          WHERE oi.order_id = :order_id");
    $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Функция для получения популярных товаров
function get_featured_products($pdo, $limit = 3) {
    try {
        // Проверяем наличие столбца is_featured в таблице products
        $stmt = $pdo->prepare("SHOW COLUMNS FROM products LIKE 'is_featured'");
        $stmt->execute();
        $column_exists = $stmt->rowCount() > 0;
        
        // Если столбец is_featured существует, используем его
        if ($column_exists) {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE is_featured = 1 LIMIT :limit");
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $products = $stmt->fetchAll();
            
            if (!empty($products)) {
                return $products;
            }
        }
        
        // Пробуем получить самые продаваемые товары
        try {
            $stmt = $pdo->prepare("SELECT p.*, SUM(oi.quantity) as total_sold 
                                  FROM products p 
                                  JOIN order_items oi ON p.id = oi.product_id 
                                  GROUP BY p.id 
                                  ORDER BY total_sold DESC 
                                  LIMIT :limit");
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $products = $stmt->fetchAll();
            
            if (!empty($products)) {
                return $products;
            }
        } catch (PDOException $e) {
            // Если возникла ошибка (например, таблица order_items не существует), игнорируем
        }
        
        // Если предыдущие методы не сработали, берем последние добавленные товары
        $stmt = $pdo->prepare("SELECT * FROM products ORDER BY id DESC LIMIT :limit");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll();
        
        return $products;
    } catch (PDOException $e) {
        // В случае любой ошибки возвращаем пустой массив
        return [];
    }
}

// Функция для отправки обратной связи
function submit_feedback($pdo, $user_id, $name, $email, $subject, $message) {
    $stmt = $pdo->prepare("INSERT INTO feedback (user_id, name, email, subject, message) 
                          VALUES (:user_id, :name, :email, :subject, :message)");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':subject', $subject, PDO::PARAM_STR);
    $stmt->bindParam(':message', $message, PDO::PARAM_STR);
    return $stmt->execute();
}
?> 