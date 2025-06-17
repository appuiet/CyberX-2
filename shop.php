<div style="display: flex; flex-direction: column; min-height: 100vh;">
<?php
$page_title = 'Магазин';
$additional_css = 'shop.css';
include 'includes/header.php';

// Получаем все категории из базы данных
$categories = get_all_categories($pdo);

// Получаем популярные товары
$featured_products = get_featured_products($pdo, 3); // Предполагаем, что такая функция существует

// Получаем товары в корзине для проверки
$cart_items = get_cart_items();
?>

<head>
<meta name="viewport" content="width=1600, height=1100, initial-scale=1.0">
</head>

<div class="shop-container">
    <div class="shop-header">
        <h1 class="welcome-text">Добро пожаловать в наш магазин!</h1>
        <p class="shop-description">Выберите категорию товаров или воспользуйтесь поиском для быстрого нахождения нужного товара</p>
    </div>
    
    <div class="categories-container">
        <?php if (!empty($categories)): ?>
            <?php foreach ($categories as $category): ?>
                <div class="category-card" >
                    <a href="category.php?id=<?php echo $category['id']; ?>">
                        <div class="category-image" style="
    display: flex;
    height: 240px;
    justify-content: center;
">
                            <img src="<?php echo !empty($category['image']) ? 'IMG/' . $category['image'] : 'IMG/category-default.jpg'; ?>" alt="<?php echo htmlspecialchars($category['name']); ?>" style="
    width: 300px;
">
                            <?php if (isset($category['product_count']) && $category['product_count'] > 0): ?>
                                <span class="category-badge"><?php echo $category['product_count']; ?> товаров</span>
                            <?php endif; ?>
                        </div>
                        <div class="category-info">
                            <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                            <p><?php echo htmlspecialchars($category['description']); ?></p>
                            <div class="category-action">
                                <span class="view-category">Просмотреть <i class="fas fa-arrow-right"></i></span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-categories">
                <p>В данный момент категории товаров не доступны. Пожалуйста, загляните позже.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="featured-products">
        <h2>Популярные товары</h2>
        <div class="products-grid">
            <?php if (!empty($featured_products)): ?>
                <?php foreach ($featured_products as $product): ?>
                    <?php $in_cart = isset($cart_items[$product['id']]); ?>
                    <div class="product-card">
                        <div class="product-image" style="
    display: flex;
    justify-content: center;
    align-items: center;
">
                            <img src="<?php echo !empty($product['image']) ? $product['image'] : 'IMG/product-default.jpg'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="
    width: 300px;
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
                                <div class="product-price-container">
                                    <?php if (isset($product['old_price']) && $product['old_price'] > $product['price']): ?>
                                        <span class="old-price"><?php echo format_price($product['old_price']); ?> BYN</span>
                                    <?php endif; ?>
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
                <!-- Если нет данных о популярных товарах, показываем примеры -->
                <div class="product-card">
                    <div class="product-image">
                        <img src="IMG/one.png" alt="Видеокарта">
                        <span class="product-badge">-10%</span>
                    </div>
                    <div class="product-info">
                        <h3>NVIDIA GeForce RTX 4080</h3>
                        <div class="product-description">
                            Мощная видеокарта для игр и профессиональных задач с поддержкой трассировки лучей.
                        </div>
                        <div class="product-meta">
                            <div class="product-price-container">
                                <span class="old-price">2199 BYN</span>
                                <p class="product-price">1999 BYN</p>
                            </div>
                            <span class="stock-status in-stock"><i class="fas fa-check-circle"></i> В наличии</span>
                        </div>
                        <div class="product-actions">
                            <div class="action-buttons">
                                <form action="cart_ajax.php" method="post" class="add-to-cart-form">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="1">
                                    <input type="hidden" name="price" value="1999">
                                    <button type="submit" class="add-to-cart-btn">В корзину <i class="fas fa-cart-plus"></i></button>
                                </form>
                                <button class="quick-view-btn" data-product-id="1"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-image">
                        <img src="IMG/two.png" alt="Процессор">
                    </div>
                    <div class="product-info">
                        <h3>Intel Core i9-13900K</h3>
                        <div class="product-description">
                            Высокопроизводительный процессор для геймеров и контент-мейкеров.
                        </div>
                        <div class="product-meta">
                            <div class="product-price-container">
                                <p class="product-price">1499 BYN</p>
                            </div>
                            <span class="stock-status in-stock"><i class="fas fa-check-circle"></i> В наличии</span>
                        </div>
                        <div class="product-actions">
                            <div class="action-buttons">
                                <form action="cart_ajax.php" method="post" class="add-to-cart-form">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="2">
                                    <input type="hidden" name="price" value="1499">
                                    <button type="submit" class="add-to-cart-btn">В корзину <i class="fas fa-cart-plus"></i></button>
                                </form>
                                <button class="quick-view-btn" data-product-id="2"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-image">
                        <img src="IMG/p.png" alt="Гарнитура">
                    </div>
                    <div class="product-info">
                        <h3>HyperX Cloud Alpha</h3>
                        <div class="product-description">
                            Профессиональная игровая гарнитура с отличным звучанием и комфортом.
                        </div>
                        <div class="product-meta">
                            <div class="product-price-container">
                                <p class="product-price">399 BYN</p>
                            </div>
                            <span class="stock-status in-stock"><i class="fas fa-check-circle"></i> В наличии</span>
                        </div>
                        <div class="product-actions">
                            <div class="action-buttons">
                                <form action="cart_ajax.php" method="post" class="add-to-cart-form">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="3">
                                    <input type="hidden" name="price" value="399">
                                    <button type="submit" class="add-to-cart-btn">В корзину <i class="fas fa-cart-plus"></i></button>
                                </form>
                                <button class="quick-view-btn" data-product-id="3"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Подключаем модальные окна для страниц магазина
// include 'includes/modals.php';

// Подключаем футер
include 'includes/footer.php';
?>

<script>
// Универсальный обработчик для всех форм добавления в корзину и проверки состояния кнопок
function setupCartButtons() {
    console.log('Инициализация setupCartButtons()');
    
    // Очистка существующих обработчиков событий
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    addToCartButtons.forEach(button => {
        button.replaceWith(button.cloneNode(true));
    });
    
    // Глобальный флаг для отслеживания отправки запросов
    window.isSubmittingCart = false;
    
    // Находим все формы добавления в корзину
    const addToCartForms = document.querySelectorAll('.add-to-cart-form');
    console.log('Найдено форм:', addToCartForms.length);
    
    // Отключаем все формы, чтобы предотвратить стандартную отправку
    addToCartForms.forEach(form => {
        // Полностью отключаем стандартную отправку формы
        form.setAttribute('onsubmit', 'return false;');
        form.action = '#';
        form.method = 'GET'; // Меняем метод на GET, чтобы исключить отправку даже если JavaScript отключен
    });
    
    // Устанавливаем обработчики на кнопки
    const newButtons = document.querySelectorAll('.add-to-cart-btn');
    console.log('Найдено кнопок:', newButtons.length);
    
    newButtons.forEach(button => {
        button.addEventListener('click', handleAddToCart);
    });
    
    function handleAddToCart(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Проверяем активность кнопки
        if (this.disabled || this.classList.contains('added')) {
            console.log('Кнопка неактивна или товар уже в корзине');
            return false;
        }
        
        // Проверяем, не происходит ли уже отправка запроса
        if (window.isSubmittingCart) {
            console.log('Запрос уже выполняется, отменяем дублирование');
            return false;
        }
        
        // Устанавливаем флаг отправки
        window.isSubmittingCart = true;
        console.log('Установлен флаг isSubmittingCart =', window.isSubmittingCart);
        
        const form = this.closest('.add-to-cart-form');
        const productId = form.querySelector('input[name="product_id"]').value;
        const price = form.querySelector('input[name="price"]') ? form.querySelector('input[name="price"]').value : null;
        
        console.log('ID товара:', productId, 'Цена:', price);
        
        this.innerHTML = 'Добавление... <i class="fas fa-spinner fa-spin"></i>';
        this.disabled = true;
        
        // Формируем данные для запроса
        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('product_id', productId);
        if (price !== null) formData.append('price', price);
        
        console.log('Отправка AJAX запроса в cart_ajax.php');
        
        // Сохраняем ссылку на кнопку
        const button = this;
        
        // Отправляем запрос
        fetch('cart_ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('Ответ получен:', data);
            
            // Сбрасываем флаг отправки
            window.isSubmittingCart = false;
            console.log('Сброшен флаг isSubmittingCart =', window.isSubmittingCart);
            
            if (data.success) {
                button.innerHTML = 'В корзине <i class="fas fa-check"></i>';
                button.classList.add('added');
                button.disabled = true;
                
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
                button.innerHTML = 'В корзину <i class="fas fa-cart-plus"></i>';
                button.disabled = false;
                console.error('Ошибка при добавлении товара:', data.message);
                alert('Ошибка при добавлении товара в корзину: ' + (data.message || 'Неизвестная ошибка'));
            }
        })
        .catch(error => {
            console.error('Ошибка сети при отправке запроса:', error);
            button.innerHTML = 'В корзину <i class="fas fa-cart-plus"></i>';
            button.disabled = false;
            window.isSubmittingCart = false; // Сбрасываем флаг отправки
            console.log('Сброшен флаг isSubmittingCart =', window.isSubmittingCart);
            alert('Ошибка сети при отправке запроса');
        });
        
        return false; // Предотвращаем дальнейшую обработку события
    }
}

// Проверка состояния кнопок при загрузке страницы
function checkCartItems() {
    console.log('Инициализация checkCartItems()');
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

// Добавляем обработчик событий при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM загружен, инициализация функций');
    // Вызываем функцию для настройки кнопок добавления в корзину
    setupCartButtons();
    // Проверяем состояние корзины
    checkCartItems();
});
</script> 