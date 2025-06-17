<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = 'Профиль';
$additional_css = 'profile.css';
include 'includes/header.php';

// Проверяем, авторизован ли пользователь
if (!is_logged_in()) {
    $_SESSION['error_message'] = 'Для доступа к профилю необходимо авторизоваться';
    header('Location: login.php');
    exit;
}

// Получаем информацию о пользователе
$user_info = get_user_info($pdo, $_SESSION['user_id']);

// Получаем текущую вкладку
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'profile';

// Получаем заказы пользователя для вкладки заказов
$orders = [];
if ($active_tab === 'orders') {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = :user_id AND status != 'cart' ORDER BY created_at DESC");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $orders = $stmt->fetchAll();
}

// Получаем товары в корзине для вкладки корзины
$cart_items = [];
if ($active_tab === 'cart') {
    $cart_items = get_cart_details($pdo);
    $cart_total = get_cart_total();
}
?>

<head>
<meta name="viewport" content="width=1600, height=1100, initial-scale=1.0">
</head>

<div class="profile-container">
    <div class="profile-sidebar">
        <div class="profile-avatar">
            <h3><?php echo htmlspecialchars($user_info['username']); ?></h3>
            <p><?php echo htmlspecialchars($user_info['email']); ?></p>
        </div>
        
        <ul class="profile-menu">
            <li class="<?php echo $active_tab === 'profile' ? 'active' : ''; ?>">
                <a href="?tab=profile"><i class="fas fa-user"></i> Личные данные</a>
            </li>
            <li class="<?php echo $active_tab === 'orders' ? 'active' : ''; ?>">
                <a href="?tab=orders"><i class="fas fa-shopping-bag"></i> Мои заказы</a>
            </li>
            <li class="<?php echo $active_tab === 'cart' ? 'active' : ''; ?>">
                <a href="?tab=cart"><i class="fas fa-shopping-cart"></i> Корзина <span class="cart-count"><?php echo get_cart_count(); ?></span></a>
            </li>
            <li class="<?php echo $active_tab === 'settings' ? 'active' : ''; ?>">
                <a href="?tab=settings"><i class="fas fa-cog"></i> Настройки</a>
            </li>
            <li>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Выйти</a>
            </li>
        </ul>
    </div>
    
    <div class="profile-content">
        <?php if ($active_tab === 'profile'): ?>
            <div class="profile-section">
                <h2>Личные данные</h2>
                
                <div class="profile-info">
                    <div class="info-group">
                        <label>Имя пользователя:</label>
                        <p><?php echo htmlspecialchars($user_info['username']); ?></p>
                    </div>
                    
                    <div class="info-group">
                        <label>Email:</label>
                        <p><?php echo htmlspecialchars($user_info['email']); ?></p>
                    </div>
                    
                    <div class="info-group">
                        <label>ФИО:</label>
                        <p><?php echo htmlspecialchars($user_info['full_name'] ?? 'Не указано'); ?></p>
                    </div>
                    
                    <div class="info-group">
                        <label>Телефон:</label>
                        <p><?php echo htmlspecialchars($user_info['phone'] ?? 'Не указано'); ?></p>
                    </div>
                </div>
                
                <div class="profile-actions">
                    <a href="?tab=settings" class="btn" style="margin-top: 2rem;">Редактировать профиль</a>
                </div>
            </div>
        
        <?php elseif ($active_tab === 'orders'): ?>
            <div class="profile-section">
                <h2>Мои заказы</h2>
                
                <?php if (empty($orders)): ?>
                    <div class="empty-orders">
                        <p>У вас пока нет заказов</p>
                        <a href="shop.php" class="btn">Перейти в магазин</a>
                    </div>
                <?php else: ?>
                    <div class="orders-list">
                        <?php foreach ($orders as $order): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <div class="order-info">
                                        <span class="order-number">Заказ #<?php echo $order['id']; ?></span>
                                        <span class="order-date"><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></span>
                                    </div>
                                    <div class="order-status <?php echo $order['status']; ?>">
                                        <?php 
                                        $status_text = 'В обработке';
                                        switch ($order['status']) {
                                            case 'pending': $status_text = 'В обработке'; break;
                                            case 'processing': $status_text = 'Комплектуется'; break;
                                            case 'shipped': $status_text = 'Отправлен'; break;
                                            case 'delivered': $status_text = 'Доставлен'; break;
                                            case 'completed': $status_text = 'Выполнен'; break;
                                            case 'cancelled': $status_text = 'Отменен'; break;
                                        }
                                        echo $status_text;
                                        ?>
                                    </div>
                                </div>
                                
                                <div class="order-items">
                                    <?php
                                    // Получаем товары заказа
                                    $stmt = $pdo->prepare("
                                        SELECT oi.*, p.name, p.image 
                                        FROM order_items oi 
                                        JOIN products p ON oi.product_id = p.id 
                                        WHERE oi.order_id = :order_id
                                    ");
                                    $stmt->bindParam(':order_id', $order['id'], PDO::PARAM_INT);
                                    $stmt->execute();
                                    $order_items = $stmt->fetchAll();
                                    
                                    foreach ($order_items as $item):
                                    ?>
                                        <div class="order-item">
                                            <div class="item-image">
                                                <img src="<?php echo !empty($item['image']) ? $item['image'] : 'IMG/product-default.jpg'; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                            </div>
                                            <div class="item-details">
                                                <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                                <p class="item-price"><?php echo format_price($item['price']); ?> BYN × <?php echo $item['quantity']; ?></p>
                                            </div>
                                            <div class="item-total">
                                                <?php echo format_price($item['price'] * $item['quantity']); ?> BYN
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="order-footer">
                                    <div class="order-total">
                                        Итого: <strong><?php echo format_price($order['total_amount']); ?> BYN</strong>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
        <?php elseif ($active_tab === 'cart'): ?>
            <div class="profile-section">
                <h2>Корзина</h2>
                
                <?php if (empty($cart_items)): ?>
                    <div class="empty-cart">
                        <p>Ваша корзина пуста</p>
                        <a href="shop.php" class="btn">Перейти в магазин</a>
                    </div>
                <?php else: ?>
                    <div class="cart-items-compact">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item-row" data-product-id="<?php echo $item['product_id']; ?>">
                                <div class="cart-item-row-image">
                                    <img src="<?php echo !empty($item['image']) ? $item['image'] : 'IMG/product-default.jpg'; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="cart-item-row-info">
                                    <h4 class="cart-item-row-name"><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <div class="cart-item-row-details">
                                        <span class="cart-item-row-price"><?php echo format_price($item['price']); ?> BYN</span>
                                        <span class="cart-item-row-quantity">x <?php echo $item['quantity']; ?></span>
                                        <span class="cart-item-row-subtotal"><?php echo format_price($item['subtotal']); ?> BYN</span>
                                    </div>
                                </div>
                                <button type="button" class="remove-item-btn" data-product-id="<?php echo $item['product_id']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="cart-total">
                            <div class="cart-total-label">Итого:</div>
                            <div class="cart-total-amount"><?php echo format_price($cart_total); ?> BYN</div>
                        </div>
                        
                        <div class="cart-actions">
                            <a href="cart.php" class="btn">Перейти в корзину</a>
                            <form method="post" action="cart.php">
                                <button type="submit" name="checkout" class="checkout-btn">
                                    <i class="fas fa-credit-card"></i> Оформить заказ
                                </button>
                            </form>
                        </div>
                    </div>                    
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Обработка кнопок удаления товара
                        const removeButtons = document.querySelectorAll('.remove-item-btn');
                        
                        if (removeButtons) {
                            removeButtons.forEach(button => {
                                button.addEventListener('click', function() {
                                    const productId = this.dataset.productId;
                                    const row = this.closest('.cart-item-row');
                                    
                                    if (confirm('Вы уверены, что хотите удалить этот товар из корзины?')) {
                                        // Отправляем AJAX-запрос для удаления товара
                                        const formData = new FormData();
                                        formData.append('action', 'remove');
                                        formData.append('product_id', productId);
                                        
                                        fetch('cart_ajax.php', {
                                            method: 'POST',
                                            body: formData
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success) {
                                                // Удаляем строку из списка
                                                row.style.opacity = '0';
                                                setTimeout(() => {
                                                    row.remove();
                                                    
                                                    // Обновляем общую сумму
                                                    const cartTotalElement = document.querySelector('.cart-total-amount');
                                                    if (cartTotalElement) {
                                                        cartTotalElement.textContent = data.cart_total;
                                                    }
                                                    
                                                    // Обновляем счетчик в шапке
                                                    const cartBadge = document.querySelector('.cart-badge');
                                                    if (cartBadge) {
                                                        cartBadge.textContent = data.cart_count;
                                                        cartBadge.style.display = data.cart_count > 0 ? 'inline-flex' : 'none';
                                                    }
                                                    
                                                    // Если корзина пуста, перезагружаем страницу
                                                    if (data.cart_count === 0) {
                                                        location.reload();
                                                    }
                                                }, 300);
                                            }
                                        })
                                        .catch(error => console.error('Ошибка:', error));
                                    }
                                });
                            });
                        }
                    });
                    </script>
                <?php endif; ?>
            </div>
            
        <?php elseif ($active_tab === 'settings'): ?>
            <div class="profile-section">
                <h2>Настройки профиля</h2>
                
                <form action="update_profile.php" method="post" class="settings-form">
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_info['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">ФИО:</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user_info['full_name'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Телефон:</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user_info['phone'] ?? ''); ?>">
                    </div>
                    
                    <button type="submit" class="btn">Сохранить изменения</button>
                </form>
                
                <h3>Изменение пароля</h3>
                
                <form action="change_password.php" method="post" class="settings-form">
                    <div class="form-group">
                        <label for="current_password">Текущий пароль:</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">Новый пароль:</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Подтвердите пароль:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn">Изменить пароль</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Обработка кнопок изменения количества в корзине
    const quantityButtons = document.querySelectorAll('.cart-item-quantity .quantity-btn');
    if (quantityButtons) {
        quantityButtons.forEach(button => {
            button.addEventListener('click', function() {
                const cartItem = this.closest('.cart-item');
                const productId = cartItem.dataset.productId;
                const action = this.dataset.action;
                const quantityElement = this.parentElement.querySelector('span');
                let quantity = parseInt(quantityElement.textContent);
                
                if (action === 'increase') {
                    quantity++;
                } else if (action === 'decrease' && quantity > 1) {
                    quantity--;
                } else {
                    return;
                }
                
                // Отправляем AJAX-запрос для обновления количества
                const formData = new FormData();
                formData.append('action', 'update');
                formData.append('product_id', productId);
                formData.append('quantity', quantity);
                
                fetch('cart_ajax.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Обновляем отображение количества
                        quantityElement.textContent = quantity;
                        
                        // Обновляем подытог для товара
                        const price = parseFloat(cartItem.querySelector('.cart-item-price').textContent.replace(' BYN', '').replace(',', '.'));
                        const subtotal = price * quantity;
                        cartItem.querySelector('.cart-item-subtotal').textContent = subtotal.toFixed(2).replace('.', ',') + ' BYN';
                        
                        // Обновляем общую сумму
                        const cartTotalElement = document.querySelector('.cart-total-amount');
                        if (cartTotalElement) {
                            cartTotalElement.textContent = data.cart_total;
                        }
                        
                        // Обновляем счетчик в меню
                        const cartCount = document.querySelector('.cart-count');
                        if (cartCount) {
                            cartCount.textContent = data.cart_count;
                        }
                    }
                })
                .catch(error => console.error('Ошибка:', error));
            });
        });
    }
    
    // Обработка кнопок удаления товара из корзины
    const removeButtons = document.querySelectorAll('.remove-item-btn');
    if (removeButtons) {
        removeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const cartItem = this.closest('.cart-item');
                const productId = this.dataset.productId;
                
                if (confirm('Вы уверены, что хотите удалить этот товар из корзины?')) {
                    // Отправляем AJAX-запрос для удаления товара
                    const formData = new FormData();
                    formData.append('action', 'remove');
                    formData.append('product_id', productId);
                    
                    fetch('cart_ajax.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Удаляем элемент из DOM
                            cartItem.style.opacity = '0';
                            setTimeout(() => {
                                cartItem.remove();
                                
                                // Обновляем общую сумму
                                const cartTotalElement = document.querySelector('.cart-total-amount');
                                if (cartTotalElement) {
                                    cartTotalElement.textContent = data.cart_total;
                                }
                                
                                // Обновляем счетчик в меню
                                const cartCount = document.querySelector('.cart-count');
                                if (cartCount) {
                                    cartCount.textContent = data.cart_count;
                                }
                                
                                // Если корзина пуста, перезагружаем страницу
                                if (data.cart_count === 0) {
                                    location.reload();
                                }
                            }, 300);
                        }
                    })
                    .catch(error => console.error('Ошибка:', error));
                }
            });
        });
    }
});
</script>

</div>
<?php include 'includes/footer.php'; ?>

