<?php
// Файл с модальными окнами для страниц магазина
?>
<!-- Модальное окно для быстрого просмотра товара -->
<div id="quickViewModal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <div id="quickViewContent">
            <!-- Содержимое будет загружено через AJAX -->
            <div class="loading">
                <i class="fas fa-spinner fa-spin"></i> Загрузка...
            </div>
        </div>
    </div>
</div>

<!-- Уведомление о добавлении в корзину -->
<div id="cartNotification" class="cart-notification">
    <div class="cart-notification-content">
        <div class="cart-notification-image">
            <img src="" alt="Товар" id="cartNotificationImage">
        </div>
        <div class="cart-notification-info">
            <h4>Товар добавлен в корзину</h4>
            <p id="cartNotificationName"></p>
            <div class="cart-notification-actions">
                <a href="cart.php" class="view-cart-btn">Перейти в корзину</a>
                <button class="continue-shopping-btn">Продолжить покупки</button>
            </div>
        </div>
        <button class="close-notification">&times;</button>
    </div>
</div> 