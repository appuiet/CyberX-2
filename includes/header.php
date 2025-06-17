<?php
// Запускаем сессию в начале файла
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/cart.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - CYBERX' : 'CYBERX'; ?></title>
    <link rel="stylesheet" href="styles/base.css">
    <link rel="stylesheet" href="styles/header.css">
    <link rel="stylesheet" href="styles/footer.css">
    <link rel="stylesheet" href="styles/section.css">
    <link rel="stylesheet" href="styles/shop.css">
    <?php if (isset($additional_css)): ?>
        <link rel="stylesheet" href="styles/<?php echo $additional_css; ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    </head>
<body>
<header class="header">
  <div class="header__container">

    <a href="index.php" class="header__logo">
      <img src="IMG/logo-2.svg" alt="CyberX Logo" width="150" height="50">
    </a>

    <button class="header__toggle" aria-label="Меню">
      <span class="header__toggle-icon"></span>
    </button>

    <div class="header__left">
      <nav class="header__menu">
        <ul class="header__list">
          <li class="header__item"><a href="index.php" class="header__item-link">Главная</a></li>
          <li class="header__item"><a href="shop.php" class="header__item-link">Магазин</a></li>
          <li class="header__item"><a href="onas.php" class="header__item-link">О нас</a></li>
          <li class="header__item"><a href="contacts.php" class="header__item-link">Контакты</a></li>
          <li class="header__item"><a href="feedback.php" class="header__item-link">Обратная связь</a></li>
        </ul>
      </nav>

      <div class="header__auth">
        <?php if (is_logged_in()): ?>
          <a href="profile.php" class="auth-link">Профиль</a>
          <a href="cart.php" class="auth-link">
            Корзина
            <?php if (get_cart_count() > 0): ?>
              <span class="cart-badge"><?php echo get_cart_count(); ?></span>
            <?php endif; ?>
          </a>
          <?php if (is_admin()): ?>
            <a href="admin/index.php" class="auth-link">Админ</a>
          <?php endif; ?>
          <a href="logout.php" class="auth-link logout">Выйти</a>
        <?php else: ?>
          <a href="login.php" class="auth-button">Вход</a>
          <a href="register.php" class="auth-button">Регистрация</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</header>

<!-- Backdrop overlay for mobile navigation -->
<div class="header__backdrop"></div>

    <div class="page-wrapper">
        <?php
        // Вывод сообщений об ошибках или успехе
        if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $_SESSION['error_message']; ?>
                <button class="close-alert"><i class="fas fa-times"></i></button>
            </div>
        <?php
            unset($_SESSION['error_message']);
        endif;
        
        if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $_SESSION['success_message']; ?>
                <button class="close-alert"><i class="fas fa-times"></i></button>
            </div>
        <?php
            unset($_SESSION['success_message']);
        endif;
        ?>
        
        <div class="content">
            <!-- Здесь будет контент страницы -->
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.querySelector('.header__toggle');
            const left = document.querySelector('.header__left');
            const body = document.body;
            const backdrop = document.querySelector('.header__backdrop');

            // Открытие/закрытие навигации
            toggle.addEventListener('click', function() {
                toggle.classList.toggle('is-open');
                left.classList.toggle('is-open');
                body.classList.toggle('no-scroll');
                if (backdrop) {
                    backdrop.classList.toggle('is-active');
                }
            });

            // Закрытие при клике на подложку
            if (backdrop) {
                backdrop.addEventListener('click', function () {
                    toggle.classList.remove('is-open');
                    left.classList.remove('is-open');
                    backdrop.classList.remove('is-active');
                    body.classList.remove('no-scroll');
                });
            }

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
        });
    </script>
</body>
</html> 