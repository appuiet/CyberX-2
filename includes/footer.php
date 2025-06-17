    </div> <!-- Закрываем .content из header.php -->
    </main> <!-- Закрываем .main-content из header.php -->
    </div> <!-- Закрываем .page-wrapper из header.php -->
    
    
    <footer class="footer">
        <div class="footer__container">
            <div class="footer__logo">
                <img src="/images/logo-2.svg" alt="CyberX Logo">
            </div>
            <div class="footer__info">
                <p>© 2024 CyberX. Все права защищены.</p>
                <p>Компьютерные комплектующие и периферия</p>
            </div>
            <div class="footer__social">
                <div class="footer__social-text">Мы в соцсетях</div>
                <div class="footer__icons">
                    <!-- <a href="https://vk.com" target="_blank" rel="noopener">
                        <i class="icon-vk"></i>
                    </a> -->
                    <a href="https://instagram.com" target="_blank" rel="noopener">
                        <i class="icon-instagram"></i>
                    </a>
                    <a href="https://tiktok.com" target="_blank" rel="noopener">
                        <i class="icon-tiktok"></i>
                    </a>
                    <a href="https://twitch.tv" target="_blank" rel="noopener">
                        <i class="icon-twitch"></i>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript для улучшения взаимодействия с пользователем -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Закрытие уведомлений
            const closeButtons = document.querySelectorAll('.close-alert');
            if (closeButtons) {
                closeButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        this.parentElement.style.opacity = '0';
                        setTimeout(() => {
                            this.parentElement.style.display = 'none';
                        }, 300);
                    });
                });
            }
            
            // Переключение боковой панели на мобильных устройствах
            const toggleButton = document.querySelector('.toggle-sidebar');
            if (toggleButton) {
                toggleButton.addEventListener('click', function() {
                    const sidebar = document.querySelector('.sidebar');
                    const mainContent = document.querySelector('.main-content');
                    
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');
                    
                    // Сохраняем состояние меню в localStorage
                    if (sidebar.classList.contains('collapsed')) {
                        localStorage.setItem('sidebar-collapsed', 'true');
                    } else {
                        localStorage.setItem('sidebar-collapsed', 'false');
                    }
                });
                
                // Восстанавливаем состояние меню при загрузке страницы
                const sidebarState = localStorage.getItem('sidebar-collapsed');
                if (sidebarState === 'true') {
                    document.querySelector('.sidebar').classList.add('collapsed');
                    document.querySelector('.main-content').classList.add('expanded');
                }
            }
            
            // Анимация для форм
            const formInputs = document.querySelectorAll('.auth-form input');
            if (formInputs) {
                formInputs.forEach(input => {
                    // При загрузке страницы проверяем, есть ли значение в поле
                    if (input.value !== '') {
                        input.parentElement.classList.add('input-focused');
                    }
                    
                    input.addEventListener('focus', function() {
                        this.parentElement.classList.add('input-focused');
                    });
                    
                    input.addEventListener('blur', function() {
                        if (this.value === '') {
                            this.parentElement.classList.remove('input-focused');
                        }
                    });
                });
            }
            
            // AJAX добавление товаров в корзину
            const addToCartForms = document.querySelectorAll('form[action="cart_ajax.php"]');
            const cartBadge = document.querySelector('.cart-badge');
            const cartNotification = document.getElementById('cartNotification');
            
            if (addToCartForms && cartNotification) {
                const cartNotificationName = document.getElementById('cartNotificationName');
                const cartNotificationImage = document.getElementById('cartNotificationImage');
                const closeNotification = document.querySelector('.close-notification');
                const continueShoppingBtn = document.querySelector('.continue-shopping-btn');
                
                addToCartForms.forEach(form => {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        const formData = new FormData(this);
                        const submitBtn = this.querySelector('button[type="submit"]');
                        
                        // Блокируем кнопку и показываем индикатор загрузки
                        if (submitBtn) {
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Добавление...';
                        }
                        
                        // Отправляем AJAX-запрос
                        fetch('cart_ajax.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            // Восстанавливаем кнопку, но делаем её серой
                            if (submitBtn) {
                                submitBtn.disabled = true;
                                submitBtn.innerHTML = 'В корзине <i class="fas fa-check"></i>';
                                submitBtn.classList.add('added');
                            }
                            
                            if (data.success) {
                                // Обновляем счетчик товаров в корзине
                                if (cartBadge) {
                                    cartBadge.textContent = data.cart_count;
                                    cartBadge.style.display = data.cart_count > 0 ? 'inline-flex' : 'none';
                                    cartBadge.classList.add('updated');
                                    setTimeout(() => {
                                        cartBadge.classList.remove('updated');
                                    }, 500);
                                }
                                
                                // Обновляем счетчик в профиле, если он есть
                                const profileCartCount = document.querySelector('.cart-count');
                                if (profileCartCount) {
                                    profileCartCount.textContent = data.cart_count;
                                }
                                
                                // Показываем уведомление
                                if (cartNotification) {
                                    cartNotificationName.textContent = data.product_name;
                                    cartNotificationImage.src = data.product_image || 'IMG/product-default.jpg';
                                    cartNotification.classList.add('active');
                                    
                                    // Автоматически скрываем уведомление через 5 секунд
                                    setTimeout(() => {
                                        cartNotification.classList.remove('active');
                                    }, 5000);
                                }
                            } else {
                                alert(data.message || 'Произошла ошибка при добавлении товара в корзину');
                                
                                // Восстанавливаем кнопку в случае ошибки
                                if (submitBtn) {
                                    submitBtn.disabled = false;
                                    submitBtn.innerHTML = 'В корзину <i class="fas fa-cart-plus"></i>';
                                    submitBtn.classList.remove('added');
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Ошибка:', error);
                            alert('Произошла ошибка при обработке запроса');
                            
                            // Восстанавливаем кнопку в случае ошибки
                            if (submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = 'В корзину <i class="fas fa-cart-plus"></i>';
                                submitBtn.classList.remove('added');
                            }
                        });
                    });
                });
                
                // Закрытие уведомления о добавлении в корзину
                if (closeNotification) {
                    closeNotification.addEventListener('click', function() {
                        cartNotification.classList.remove('active');
                    });
                }
                
                // Кнопка "Продолжить покупки"
                if (continueShoppingBtn) {
                    continueShoppingBtn.addEventListener('click', function() {
                        cartNotification.classList.remove('active');
                    });
                }
            }
            
            // Быстрый просмотр товара
            const quickViewButtons = document.querySelectorAll('.quick-view-btn');
            const modal = document.getElementById('quickViewModal');
            
            if (quickViewButtons && modal) {
                const closeModal = document.querySelector('.close-modal');
                const quickViewContent = document.getElementById('quickViewContent');
                
                quickViewButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const productId = this.getAttribute('data-product-id');
                        
                        // Показываем модальное окно
                        modal.style.display = 'block';
                        modal.classList.add('show');
                        
                        // Если есть ID товара, загружаем информацию
                        if (productId) {
                            quickViewContent.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Загрузка...</div>';
                            
                            // Здесь должен быть AJAX запрос для получения информации о товаре
                            // Для демонстрации просто покажем заглушку через таймаут
                            setTimeout(() => {
                                quickViewContent.innerHTML = `
                                    <div class="product-quick-view">
                                        <div class="product-quick-image">
                                            <img src="IMG/product-default.jpg" alt="Товар">
                                        </div>
                                        <div class="product-quick-info">
                                            <h2>Демонстрационный товар</h2>
                                            <p class="product-quick-price">999 BYN</p>
                                            <div class="product-quick-description">
                                                Это демонстрационный товар для быстрого просмотра. В реальном приложении здесь будет
                                                отображаться информация о выбранном товаре, загруженная через AJAX.
                                            </div>
                                            <div class="product-quick-actions">
                                                <button class="add-to-cart-btn">В корзину <i class="fas fa-cart-plus"></i></button>
                                                <a href="#" class="view-details-btn">Подробнее</a>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            }, 500);
                        }
                    });
                });
                
                // Закрытие модального окна
                if (closeModal) {
                    closeModal.addEventListener('click', function() {
                        modal.classList.remove('show');
                        setTimeout(() => {
                            modal.style.display = 'none';
                        }, 300);
                    });
                }
                
                // Закрытие модального окна при клике вне его содержимого
                window.addEventListener('click', function(event) {
                    if (event.target == modal) {
                        modal.classList.remove('show');
                        setTimeout(() => {
                            modal.style.display = 'none';
                        }, 300);
                    }
                });
            }
        });
    </script>
</body>
</html> 