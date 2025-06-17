
<?php
// Проверяем, передан ли ID категории
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: shop.php');
    exit;
}

$category_id = (int)$_GET['id'];

$page_title = 'Категория товаров';
$additional_css = 'category.css';
include 'includes/header.php';

// Получаем информацию о категории
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
$stmt->bindParam(':id', $category_id, PDO::PARAM_INT);
$stmt->execute();
$category = $stmt->fetch();

// Если категория не найдена, перенаправляем на страницу магазина
if (!$category) {
    $_SESSION['error_message'] = 'Категория не найдена';
    header('Location: shop.php');
    exit;
}

$page_title = $category['name'];

// Получаем товары из выбранной категории
$products = get_products_by_category($pdo, $category_id);

// Получаем товары в корзине для проверки
$cart_items = get_cart_items();
?>

<div class="shop-container">
    <div class="category-header">
        <h1><?php echo htmlspecialchars($category['name']); ?></h1>
        <p><?php echo htmlspecialchars($category['description']); ?></p>
        <a href="shop.php" class="back-to-shop"><i class="fas fa-arrow-left"></i> Вернуться в магазин</a>
    </div>
    
    <div class="products-grid">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
                <?php $in_cart = isset($cart_items[$product['id']]); ?>
                <div class="product-card">
                    <div class="product-image" style="
    display: flex;
    justify-content: center;
    align-items: center;
">
                        <img src="<?php echo !empty($product['image']) ? $product['image'] : 'IMG/product-default.jpg'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="
    width: 190px;
    /* height: 149px; */
    justify-content: center;
    display: flex;
    z-index: 10000;
">
                        <?php if (isset($product['discount']) && $product['discount'] > 0): ?>
                            <span class="product-badge">-<?php echo $product['discount']; ?>%</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <div class="product-description">
                            <?php echo mb_substr(htmlspecialchars($product['description']), 0, 100) . (mb_strlen($product['description']) > 100 ? '...' : ''); ?>
                        </div>
                        <div class="product-meta">
                            <?php if (isset($product['old_price']) && $product['old_price'] > $product['price']): ?>
                                <span class="old-price"><?php echo format_price($product['old_price']); ?> BYN</span>
                            <?php endif; ?>
                            <div class="product-price-container">
                                <p class="product-price"><?php echo format_price($product['price']); ?> BYN</p>
                            </div>
                            <?php if (isset($product['stock']) && $product['stock'] > 0): ?>
                                <span class="stock-status in-stock"><i class="fas fa-check-circle"></i> В наличии</span>
                            <?php else: ?>
                                <span class="stock-status out-of-stock"><i class="fas fa-times-circle"></i> Нет в наличии</span>
                            <?php endif; ?>
                        </div>
                        <form action="cart_ajax.php" method="post" class="add-to-cart-form">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <input type="hidden" name="price" value="<?php echo $product['price']; ?>">
                            <button type="submit" class="add-to-cart-btn <?php echo $in_cart ? 'added' : ''; ?>" <?php echo $in_cart ? 'disabled' : ''; ?>>
                                <?php if ($in_cart): ?>
                                    В корзине <i class="fas fa-check"></i>
                                <?php else: ?>
                                    В корзину <i class="fas fa-cart-plus"></i>
                                <?php endif; ?>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-products">
                <p>В данной категории пока нет товаров.</p>
                <a href="shop.php" class="btn">Вернуться в магазин</a>
            </div>
        <?php endif; ?>
    </div>
</div>


<head>
<meta name="viewport" content="width=1600, height=1100, initial-scale=1.0">
</head>
<?php
// Подключаем модальные окна для страниц магазина
// include 'includes/modals.php';

// Подключаем футер
include 'includes/footer.php';
?>
</div>

<script>
// Универсальный обработчик для всех форм добавления в корзину и проверки состояния кнопок
function setupCartButtons() {
    console.log('Инициализация setupCartButtons() в category.php');
    const addToCartForms = document.querySelectorAll('.add-to-cart-form');
    console.log('Найдено форм:', addToCartForms.length);
    
    addToCartForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Перехвачена отправка формы');
            
            const submitBtn = this.querySelector('.add-to-cart-btn');
            const productId = this.querySelector('input[name="product_id"]').value;
            const price = this.querySelector('input[name="price"]') ? this.querySelector('input[name="price"]').value : null;
            
            console.log('ID товара:', productId, 'Цена:', price);
            
            submitBtn.innerHTML = 'Добавление... <i class="fas fa-spinner fa-spin"></i>';
            submitBtn.disabled = true;
            
            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('product_id', productId);
            if (price !== null) formData.append('price', price);
            
            console.log('Отправка AJAX запроса в cart_ajax.php');
            
            // Отправляем запрос напрямую через XMLHttpRequest для отладки
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'cart_ajax.php', true);
            xhr.onload = function() {
                console.log('Ответ получен. Статус:', xhr.status);
                console.log('Текст ответа:', xhr.responseText);
                
                let data;
                try {
                    data = JSON.parse(xhr.responseText);
                    console.log('Данные:', data);
                    
                    if (data.success) {
                        submitBtn.innerHTML = 'В корзине <i class="fas fa-check"></i>';
                        submitBtn.classList.add('added');
                        submitBtn.disabled = true;
                        
                        // Обновляем счетчик корзины
                        const cartBadge = document.querySelector('.cart-badge');
                        if (cartBadge) {
                            cartBadge.textContent = data.cart_count;
                            cartBadge.style.display = data.cart_count > 0 ? 'inline-flex' : 'none';
                            cartBadge.classList.add('updated');
                            setTimeout(() => {
                                cartBadge.classList.remove('updated');
                            }, 500);
                        }
                        
                        // Показываем уведомление об успешном добавлении
                        const cartNotification = document.getElementById('cartNotification');
                        if (cartNotification) {
                            const productName = document.getElementById('cartNotificationName');
                            const productImage = document.getElementById('cartNotificationImage');
                            
                            if (productName) productName.textContent = data.product_name || 'Товар';
                            if (productImage) productImage.src = data.product_image || 'IMG/product-default.jpg';
                            
                            cartNotification.classList.add('active');
                            setTimeout(() => {
                                cartNotification.classList.remove('active');
                            }, 5000);
                        }
                    } else {
                        submitBtn.innerHTML = 'В корзину <i class="fas fa-cart-plus"></i>';
                        submitBtn.disabled = false;
                        console.error('Ошибка при добавлении товара:', data.message);
                        alert('Ошибка при добавлении товара в корзину: ' + (data.message || 'Неизвестная ошибка'));
                    }
                } catch (e) {
                    console.error('Ошибка при разборе JSON:', e);
                    submitBtn.innerHTML = 'В корзину <i class="fas fa-cart-plus"></i>';
                    submitBtn.disabled = false;
                    alert('Ошибка при обработке ответа сервера');
                }
            };
            
            xhr.onerror = function() {
                console.error('Ошибка сети при отправке запроса');
                submitBtn.innerHTML = 'В корзину <i class="fas fa-cart-plus"></i>';
                submitBtn.disabled = false;
                alert('Ошибка сети при отправке запроса');
            };
            
            xhr.send(formData);
        });
    });
}

function checkCartItems() {
    console.log('Инициализация checkCartItems() в category.php');
    const productCards = document.querySelectorAll('.product-card');
    const productIds = [];
    productCards.forEach(card => {
        const productIdInput = card.querySelector('input[name="product_id"]');
        if (productIdInput) productIds.push(productIdInput.value);
    });
    
    console.log('Найдено товаров для проверки:', productIds.length);
    if (productIds.length === 0) return;
    
    console.log('Отправка запроса на проверку корзины');
    
    // Отправляем запрос через XMLHttpRequest для отладки
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'cart_ajax.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        console.log('Ответ на проверку получен. Статус:', xhr.status);
        console.log('Текст ответа:', xhr.responseText);
        
        try {
            const data = JSON.parse(xhr.responseText);
            console.log('Данные:', data);
            
            if (data.success) {
                for (const [productId, inCart] of Object.entries(data.in_cart)) {
                    console.log('Товар ID', productId, 'в корзине:', inCart);
                    
                    const form = document.querySelector(`input[name="product_id"][value="${productId}"]`).closest('form');
                    const button = form.querySelector('.add-to-cart-btn');
                    
                    if (inCart) {
                        button.innerHTML = 'В корзине <i class="fas fa-check"></i>';
                        button.classList.add('added');
                        button.disabled = true;
                    } else {
                        button.innerHTML = 'В корзину <i class="fas fa-cart-plus"></i>';
                        button.classList.remove('added');
                        button.disabled = false;
                    }
                }
                
                // Обновляем счетчик корзины
                const cartBadge = document.querySelector('.cart-badge');
                if (cartBadge) {
                    cartBadge.textContent = data.cart_count;
                    cartBadge.style.display = data.cart_count > 0 ? 'inline-flex' : 'none';
                }
            } else {
                console.error('Ошибка при проверке корзины:', data.message);
            }
        } catch (e) {
            console.error('Ошибка при разборе JSON:', e);
        }
    };
    
    xhr.onerror = function() {
        console.error('Ошибка сети при отправке запроса проверки');
    };
    
    xhr.send('action=check_cart&product_ids=' + JSON.stringify(productIds));
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM загружен, инициализация функций в category.php');
    setupCartButtons();
    checkCartItems();
});
</script> 