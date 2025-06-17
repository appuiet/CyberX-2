<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = 'Регистрация';
$additional_css = 'login.css';
include 'includes/header.php';

// Если пользователь уже авторизован, перенаправляем на главную страницу
if (is_logged_in()) {
    header('Location: profile.php');
    exit;
}

// Обработка формы регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = clean_input($_POST['email']);
    $full_name = clean_input($_POST['full_name']);
    $phone = clean_input($_POST['phone']);
    
    // Проверяем, заполнены ли все обязательные поля
    if (empty($username) || empty($password) || empty($confirm_password) || empty($email) || empty($full_name)) {
        $_SESSION['error_message'] = 'Заполните все обязательные поля';
    }
    // Проверяем, совпадают ли пароли
    elseif ($password !== $confirm_password) {
        $_SESSION['error_message'] = 'Пароли не совпадают';
    }
    // Проверяем длину пароля
    elseif (strlen($password) < 6) {
        $_SESSION['error_message'] = 'Пароль должен содержать не менее 6 символов';
    }
    // Проверяем корректность email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = 'Введите корректный email';
    }
    else {
        // Пытаемся зарегистрировать пользователя
        $result = register_user($pdo, $username, $password, $email, $full_name, $phone);
        
        if ($result['success']) {
            // Если успешно, авторизуем пользователя и перенаправляем на профиль
            login_user($email, $password);
            $_SESSION['success_message'] = $result['message'];
            header('Location: profile.php');
            exit;
        } else {
            // Если ошибка, выводим сообщение
            $_SESSION['error_message'] = $result['message'];
        }
    }
}
?>

<head>
    <link rel="stylesheet" href="/styles/register.css">
</head>

<div class="auth-container">
    <div class="auth-form-container">
        <h2><i class="fas fa-user-plus"></i> Регистрация</h2>

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

        <form action="register.php" method="post" class="auth-form">
            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Имя пользователя: *</label>
                <input type="text" id="username" name="username" placeholder="Придумайте имя пользователя" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Пароль: *</label>
                <input type="password" id="password" name="password" placeholder="Придумайте пароль" required>
                <small>Пароль должен содержать не менее 6 символов</small>
            </div>

            <div class="form-group">
                <label for="confirm_password"><i class="fas fa-lock"></i> Подтверждение пароля: *</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Повторите пароль" required>
            </div>

            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email: *</label>
                <input type="email" id="email" name="email" placeholder="Введите ваш email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="full_name"><i class="fas fa-user-circle"></i> ФИО: *</label>
                <input type="text" id="full_name" name="full_name" placeholder="Введите ваше полное имя" value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="phone"><i class="fas fa-phone"></i> Телефон:</label>
                <input type="tel" id="phone" name="phone" placeholder="+375 (XX) XXX-XX-XX" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
            </div>

            <div class="form-group terms">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms" class="checkbox-label">Я согласен с <a href="#" class="terms-link">условиями использования</a> *</label>
            </div>

            <button type="submit" class="auth-button">Зарегистрироваться <i class="fas fa-user-plus"></i></button>
        </form>

        <div class="auth-links">
            <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>
