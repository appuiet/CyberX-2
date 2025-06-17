<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$page_title = 'Вход';
$additional_css = 'login.css';
require_once 'config/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/cart.php';

// Если пользователь уже авторизован, перенаправляем на главную страницу
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Обрабатываем отправку формы
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $email = clean_input($_POST['email']);
    $password = $_POST['password'];
    
    // Проверяем, заполнены ли все поля
    if (empty($email) || empty($password)) {
        $error = 'Пожалуйста, заполните все поля';
    } else {
        // Пытаемся авторизовать пользователя
        $result = login_user($email, $password);
        
        if ($result) {
            // Если авторизация успешна, перенаправляем на страницу профиля
            header('Location: profile.php');
            exit;
        } else {
            $error = 'Неверный email или пароль';
        }
    }
}

// Подключаем header
include 'includes/header.php';
?>

<head>
<meta name="viewport" content="width=1600, height=1100, initial-scale=1.0">
</head>

<div class="auth-container">
    <div class="auth-form-container">
        <h2><i class="fas fa-sign-in-alt"></i> Вход в аккаунт</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
                <button type="button" class="close-alert">&times;</button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
                <button type="button" class="close-alert">&times;</button>
            </div>
        <?php endif; ?>
        
        <form class="auth-form" method="post" action="">
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Пароль</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="remember-me">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember" class="checkbox-label">Запомнить меня</label>
            </div>
            
            <button type="submit" class="auth-button">
                Войти <i class="fas fa-sign-in-alt"></i>
            </button>
            
            <div class="auth-links">
                <p>Еще нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
                <!-- TODO: нереализованный forgot-password.php -->
                <!-- <p><a href="forgot-password.php">Забыли пароль?</a></p> -->
            </div>
        </form>
    </div>
</div>

<?php
include 'includes/footer.php';
?>
