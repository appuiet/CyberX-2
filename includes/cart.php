<?php
// Инициализация корзины
function init_cart() {
    global $pdo;
    
    // Проверяем, авторизован ли пользователь
    if (!isset($_SESSION['user_id'])) {
        // Для неавторизованных пользователей используем сессию
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        error_log('init_cart: неавторизованный пользователь, сессионная корзина');
        return;
    }
    
    // Для авторизованных пользователей проверяем наличие корзины в БД
    $user_id = $_SESSION['user_id'];
    
    try {
        // Проверяем, есть ли у пользователя корзина
        $stmt = $pdo->prepare("SELECT id FROM cart WHERE user_id = :user_id LIMIT 1");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch();
        
        if (!$result) {
            // Если нет корзины, создаем её
            $stmt = $pdo->prepare("INSERT INTO cart (user_id) VALUES (:user_id)");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            
            // Логируем создание новой корзины для отладки
            error_log('init_cart: создана новая корзина для user_id=' . $user_id . ', cart_id=' . $pdo->lastInsertId());
        } else {
            // Логируем существующую корзину для отладки
            error_log('init_cart: найдена существующая корзина для user_id=' . $user_id . ', cart_id=' . $result['id']);
        }
    } catch (PDOException $e) {
        // Если возникла ошибка (например, таблица не существует), создаем сессионную корзину
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        error_log('init_cart: ошибка: ' . $e->getMessage());
    }
}

// Получение ID корзины пользователя
function get_cart_id() {
    global $pdo;
    if (!isset($_SESSION['user_id'])) return null;
    $user_id = $_SESSION['user_id'];
    try {
        $stmt = $pdo->prepare("SELECT id FROM cart WHERE user_id = :user_id LIMIT 1");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        if ($result) {
            error_log('get_cart_id: найден cart_id=' . $result['id'] . ' для user_id=' . $user_id);
            return $result['id'];
        } else {
            error_log('get_cart_id: корзина не найдена для user_id=' . $user_id);
            return null;
        }
    } catch (PDOException $e) {
        error_log('get_cart_id: ошибка: ' . $e->getMessage());
        return null;
    }
}

// Добавление товара в корзину
function add_to_cart($product_id, $price, $quantity = 1) {
    global $pdo;
    init_cart();
    
    // Подробное логирование для отладки
    error_log('==== НАЧАЛО add_to_cart ====');
    error_log('add_to_cart: product_id=' . $product_id . ', quantity=' . $quantity . ', price=' . $price);
    error_log('add_to_cart: user_id=' . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'нет'));
    error_log('add_to_cart: SESSION=' . print_r($_SESSION, true));
    
    // Если пользователь не авторизован, используем сессию
    if (!isset($_SESSION['user_id'])) {
        error_log('add_to_cart: неавторизованный пользователь, добавление в сессию');
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = [
                'product_id' => $product_id,
                'quantity' => $quantity,
                'price' => $price
            ];
        }
        error_log('add_to_cart: Товар добавлен в сессионную корзину: ' . print_r($_SESSION['cart'][$product_id], true));
        return true;
    }
    
    // Пользователь авторизован - сохраняем в БД
    $user_id = $_SESSION['user_id'];
    try {
        // Проверяем существование таблиц
        $tableCheck = $pdo->query("SHOW TABLES LIKE 'cart'");
        if ($tableCheck->rowCount() == 0) {
            error_log('add_to_cart: ОШИБКА - таблица cart не существует!');
            
            // Создаем таблицу cart если она не существует
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS cart (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ");
            error_log('add_to_cart: Таблица cart создана');
        }
        
        $tableCheck = $pdo->query("SHOW TABLES LIKE 'cart_items'");
        if ($tableCheck->rowCount() == 0) {
            error_log('add_to_cart: ОШИБКА - таблица cart_items не существует!');
            
            // Создаем таблицу cart_items если она не существует
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS cart_items (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    cart_id INT NOT NULL,
                    product_id INT NOT NULL,
                    quantity INT NOT NULL DEFAULT 1,
                    price DECIMAL(10,2) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (cart_id) REFERENCES cart(id) ON DELETE CASCADE,
                    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
                )
            ");
            error_log('add_to_cart: Таблица cart_items создана');
        }
        
        // Проверяем существование корзины пользователя
        $stmt = $pdo->prepare("SELECT id FROM cart WHERE user_id = :user_id LIMIT 1");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        $cart_id = $result ? $result['id'] : null;
        error_log('add_to_cart: cart_id=' . ($cart_id ?? 'нет'));
        
        // Если корзины нет, создаем новую
        if (!$cart_id) {
            $stmt = $pdo->prepare("INSERT INTO cart (user_id) VALUES (:user_id)");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $cart_id = $pdo->lastInsertId();
            error_log('add_to_cart: Создана новая корзина ID: ' . $cart_id);
        }
        
        // Проверяем, есть ли уже такой товар в корзине
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id");
        $stmt->bindParam(':cart_id', $cart_id, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $item = $stmt->fetch();
        
        // Если товар уже есть, обновляем количество
        if ($item) {
            $new_quantity = $item['quantity'] + $quantity;
            $stmt = $pdo->prepare("UPDATE cart_items SET quantity = :quantity, updated_at = NOW() WHERE id = :id");
            $stmt->bindParam(':quantity', $new_quantity, PDO::PARAM_INT);
            $stmt->bindParam(':id', $item['id'], PDO::PARAM_INT);
            $result = $stmt->execute();
            error_log('add_to_cart: Обновлено количество товара ID: ' . $product_id . ' в корзине ID: ' . $cart_id . ' до ' . $new_quantity . ' (результат: ' . ($result ? 'успешно' : 'ошибка') . ')');
        } else {
            // Если товара нет, добавляем его
            $stmt = $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, price) VALUES (:cart_id, :product_id, :quantity, :price)");
            $stmt->bindParam(':cart_id', $cart_id, PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmt->bindParam(':price', $price, PDO::PARAM_STR);
            $result = $stmt->execute();
            error_log('add_to_cart: Добавлен новый товар ID: ' . $product_id . ' в корзину ID: ' . $cart_id . ' (кол-во: ' . $quantity . ', цена: ' . $price . ') (результат: ' . ($result ? 'успешно' : 'ошибка') . ')');
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log('add_to_cart: Ошибка SQL: ' . print_r($errorInfo, true));
            }
        }
        error_log('==== КОНЕЦ add_to_cart: УСПЕХ ====');
        return true;
    } catch (Exception $e) {
        error_log('add_to_cart: Ошибка при добавлении товара в корзину: ' . $e->getMessage());
        error_log('add_to_cart: Трассировка: ' . $e->getTraceAsString());
        // ВРЕМЕННО: вывод ошибки на экран для диагностики
        if (php_sapi_name() !== 'cli') {
            echo '<pre style="color:red;">add_to_cart: Ошибка: ' . htmlspecialchars($e->getMessage()) . '</pre>';
        }
        
        // Резервное сохранение в сессию
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = [
                'product_id' => $product_id,
                'quantity' => $quantity,
                'price' => $price
            ];
        }
        error_log('==== КОНЕЦ add_to_cart: ОШИБКА ====');
        return true;
    }
}

// Обновление количества товара в корзине
function update_cart_item($product_id, $quantity) {
    global $pdo;
    init_cart();
    if (!isset($_SESSION['user_id'])) {
        if (isset($_SESSION['cart'][$product_id])) {
            if ($quantity > 0) {
                $_SESSION['cart'][$product_id]['quantity'] = $quantity;
            } else {
                remove_from_cart($product_id);
            }
            error_log('update_cart_item: обновлено в сессии product_id=' . $product_id . ', quantity=' . $quantity);
            return true;
        }
        return false;
    }
    $cart_id = get_cart_id();
    if (!$cart_id) {
        if (isset($_SESSION['cart'][$product_id])) {
            if ($quantity > 0) {
                $_SESSION['cart'][$product_id]['quantity'] = $quantity;
            } else {
                unset($_SESSION['cart'][$product_id]);
            }
            error_log('update_cart_item: не найден cart_id, обновлено в сессии product_id=' . $product_id . ', quantity=' . $quantity);
            return true;
        }
        return false;
    }
    try {
        if ($quantity > 0) {
            $stmt = $pdo->prepare("UPDATE cart_items SET quantity = :quantity, updated_at = NOW() WHERE cart_id = :cart_id AND product_id = :product_id");
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmt->bindParam(':cart_id', $cart_id, PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->execute();
            error_log('update_cart_item: обновлено в БД cart_id=' . $cart_id . ', product_id=' . $product_id . ', quantity=' . $quantity);
        } else {
            remove_from_cart($product_id);
        }
        return true;
    } catch (PDOException $e) {
        if (isset($_SESSION['cart'][$product_id])) {
            if ($quantity > 0) {
                $_SESSION['cart'][$product_id]['quantity'] = $quantity;
            } else {
                unset($_SESSION['cart'][$product_id]);
            }
        }
        error_log('update_cart_item: ошибка: ' . $e->getMessage());
        return true;
    }
}

// Удаление товара из корзины
function remove_from_cart($product_id) {
    global $pdo;
    init_cart();
    if (!isset($_SESSION['user_id'])) {
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
            error_log('remove_from_cart: удалено из сессии product_id=' . $product_id);
            return true;
        }
        return false;
    }
    $cart_id = get_cart_id();
    if (!$cart_id) {
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
            error_log('remove_from_cart: не найден cart_id, удалено из сессии product_id=' . $product_id);
            return true;
        }
        return false;
    }
    try {
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id");
        $stmt->bindParam(':cart_id', $cart_id, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        error_log('remove_from_cart: удалено из БД cart_id=' . $cart_id . ', product_id=' . $product_id);
        return true;
    } catch (PDOException $e) {
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
        }
        error_log('remove_from_cart: ошибка: ' . $e->getMessage());
        return true;
    }
}

// Очистка корзины
function clear_cart() {
    global $pdo;
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['cart'] = [];
        error_log('clear_cart: очищена сессионная корзина');
        return true;
    }
    $cart_id = get_cart_id();
    if (!$cart_id) {
        $_SESSION['cart'] = [];
        error_log('clear_cart: не найден cart_id, очищена сессионная корзина');
        return true;
    }
    try {
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = :cart_id");
        $stmt->bindParam(':cart_id', $cart_id, PDO::PARAM_INT);
        $stmt->execute();
        error_log('clear_cart: очищена корзина в БД cart_id=' . $cart_id);
        return true;
    } catch (PDOException $e) {
        $_SESSION['cart'] = [];
        error_log('clear_cart: ошибка: ' . $e->getMessage());
        return true;
    }
}

// Получение всех товаров в корзине
function get_cart_items() {
    global $pdo;
    init_cart();
    if (!isset($_SESSION['user_id'])) {
        error_log('get_cart_items: неавторизованный пользователь, сессионная корзина: ' . print_r($_SESSION['cart'], true));
        return $_SESSION['cart'];
    }
    $cart_id = get_cart_id();
    if (!$cart_id) {
        error_log('get_cart_items: не удалось получить cart_id, сессионная корзина: ' . print_r($_SESSION['cart'], true));
        return $_SESSION['cart'];
    }
    try {
        $stmt = $pdo->prepare("SELECT product_id, quantity, price FROM cart_items WHERE cart_id = :cart_id");
        $stmt->bindParam(':cart_id', $cart_id, PDO::PARAM_INT);
        $stmt->execute();
        $items = [];
        while ($item = $stmt->fetch()) {
            $items[$item['product_id']] = [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price']
            ];
        }
        error_log('get_cart_items: для пользователя ' . $_SESSION['user_id'] . ' cart_id=' . $cart_id . ' items: ' . print_r($items, true));
        return $items;
    } catch (PDOException $e) {
        error_log('get_cart_items: ошибка: ' . $e->getMessage());
        return isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
    }
}

// Получение количества товаров в корзине
function get_cart_count() {
    $items = get_cart_items();
    
    $count = 0;
    foreach ($items as $item) {
        $count += $item['quantity'];
    }
    
    return $count;
}

// Получение общей стоимости товаров в корзине
function get_cart_total() {
    $items = get_cart_items();
    
    $total = 0;
    foreach ($items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
    return $total;
}

// Получение подробной информации о товарах в корзине
function get_cart_details($pdo) {
    init_cart();
    $cart_items = get_cart_items();
    $cart_details = [];
    
    // Если корзина пуста, возвращаем пустой массив
    if (empty($cart_items)) {
        error_log('get_cart_details: корзина пуста');
        return [];
    }
    
    error_log('get_cart_details: получаем подробную информацию для ' . count($cart_items) . ' товаров');
    
    foreach ($cart_items as $product_id => $item) {
        try {
            $stmt = $pdo->prepare("SELECT id, name, image FROM products WHERE id = :product_id");
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->execute();
            $product = $stmt->fetch();
            
            if ($product) {
                $cart_details[] = [
                    'product_id' => $product_id,
                    'name' => $product['name'],
                    'price' => $item['price'],
                    'image' => $product['image'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['price'] * $item['quantity']
                ];
                error_log('get_cart_details: добавлен товар ' . $product['name'] . ' (ID: ' . $product_id . ') в детали корзины');
            } else {
                error_log('get_cart_details: товар с ID ' . $product_id . ' не найден в БД');
                // Добавляем запись даже если товар не найден, чтобы не потерять информацию
                $cart_details[] = [
                    'product_id' => $product_id,
                    'name' => 'Товар #' . $product_id . ' (не найден)',
                    'price' => $item['price'],
                    'image' => '',
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['price'] * $item['quantity']
                ];
            }
        } catch (PDOException $e) {
            error_log('get_cart_details: ошибка: ' . $e->getMessage());
            continue;
        }
    }
    
    error_log('get_cart_details: найдено ' . count($cart_details) . ' товаров');
    return $cart_details;
}

// Оформление заказа - создание заказа из корзины
function checkout_cart() {
    global $pdo;
    if (!isset($_SESSION['user_id'])) return false;
    $user_id = $_SESSION['user_id'];
    $cart_id = get_cart_id();
    if (!$cart_id) return false;
    $cart_items = get_cart_items();
    if (empty($cart_items)) return false;
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (:user_id, :total_amount, 'pending')");
        $total = get_cart_total();
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':total_amount', $total);
        $stmt->execute();
        $order_id = $pdo->lastInsertId();
        foreach ($cart_items as $product_id => $item) {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)");
            $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->bindParam(':quantity', $item['quantity'], PDO::PARAM_INT);
            $stmt->bindParam(':price', $item['price']);
            $stmt->execute();
        }
        clear_cart();
        $pdo->commit();
        error_log('checkout_cart: заказ оформлен, order_id=' . $order_id);
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('checkout_cart: ошибка: ' . $e->getMessage());
        return false;
    }
}

// Перенос товаров из сессионной корзины в БД при авторизации
function migrate_cart_to_db($user_id) {
    global $pdo;
    $cart_id = null;
    try {
        $stmt = $pdo->prepare("SELECT id FROM cart WHERE user_id = :user_id LIMIT 1");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        if ($result) {
            $cart_id = $result['id'];
            error_log('migrate_cart_to_db: найден cart_id=' . $cart_id . ' для user_id=' . $user_id);
        } else {
            $stmt = $pdo->prepare("INSERT INTO cart (user_id) VALUES (:user_id)");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $cart_id = $pdo->lastInsertId();
            error_log('migrate_cart_to_db: создана новая корзина cart_id=' . $cart_id . ' для user_id=' . $user_id);
        }
        if (!$cart_id) {
            error_log('migrate_cart_to_db: не удалось получить cart_id для user_id=' . $user_id);
            return false;
        }
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            error_log('migrate_cart_to_db: сессионная корзина пуста');
            return true;
        }
        foreach ($_SESSION['cart'] as $product_id => $item) {
            $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id");
            $stmt->bindParam(':cart_id', $cart_id, PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->execute();
            $existing_item = $stmt->fetch();
            if ($existing_item) {
                $new_quantity = $existing_item['quantity'] + $item['quantity'];
                $stmt = $pdo->prepare("UPDATE cart_items SET quantity = :quantity, updated_at = NOW() WHERE id = :id");
                $stmt->bindParam(':quantity', $new_quantity, PDO::PARAM_INT);
                $stmt->bindParam(':id', $existing_item['id'], PDO::PARAM_INT);
                $stmt->execute();
                error_log('migrate_cart_to_db: обновлено количество товара product_id=' . $product_id . ' в cart_id=' . $cart_id . ' до ' . $new_quantity);
            } else {
                $stmt = $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, price) VALUES (:cart_id, :product_id, :quantity, :price)");
                $stmt->bindParam(':cart_id', $cart_id, PDO::PARAM_INT);
                $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
                $stmt->bindParam(':quantity', $item['quantity'], PDO::PARAM_INT);
                $stmt->bindParam(':price', $item['price'], PDO::PARAM_STR);
                $stmt->execute();
                error_log('migrate_cart_to_db: добавлен товар product_id=' . $product_id . ' в cart_id=' . $cart_id);
            }
        }
        $_SESSION['cart'] = [];
        error_log('migrate_cart_to_db: сессионная корзина очищена');
        return true;
    } catch (PDOException $e) {
        error_log('migrate_cart_to_db: ошибка: ' . $e->getMessage());
        return false;
    }
}
?> 