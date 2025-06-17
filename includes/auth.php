<?php
// Подключаем файлы с функциями
require_once 'functions.php';

// Функция для регистрации пользователя
function register_user($pdo, $username, $password, $email, $full_name, $phone = null) {
    // Проверяем, существует ли пользователь с таким именем или email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        return ['success' => false, 'message' => 'Пользователь с таким именем или email уже существует'];
    }
    
    // Хешируем пароль
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Добавляем пользователя в базу данных
    $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, phone) 
                          VALUES (:username, :password, :email, :full_name, :phone)");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':full_name', $full_name, PDO::PARAM_STR);
    $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Регистрация успешно завершена'];
    } else {
        return ['success' => false, 'message' => 'Ошибка при регистрации'];
    }
}

// Функция для авторизации пользователя
function login_user($email, $password) {
    global $pdo;
    
    // Логирование для отладки
    error_log("Попытка входа: email=" . $email);
    
    // Проверяем, существует ли пользователь с таким email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email OR username = :email");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch();
    
    if (!$user) {
        error_log("Пользователь не найден: " . $email);
        return false;
    }
    
    error_log("Пользователь найден: id=" . $user['id'] . ", username=" . $user['username']);
    
    // Проверяем пароль
    if (!password_verify($password, $user['password'])) {
        error_log("Неверный пароль для пользователя: " . $user['username'] . " (введенный пароль: " . substr($password, 0, 1) . "****)");
        return false;
    }
    
    error_log("Успешная авторизация: " . $user['username']);
    
    // Если все проверки пройдены, авторизуем пользователя
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    
    // Переносим товары из сессионной корзины в БД
    if (function_exists('migrate_cart_to_db')) {
        migrate_cart_to_db($user['id']);
    }
    
    return true;
}

// Функция для выхода пользователя
function logout_user() {
    // Уничтожаем сессию
    session_unset();
    session_destroy();
    
    // Перенаправляем на главную страницу
    redirect('index.php');
}

// Функция для получения информации о пользователе
function get_user_info($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT id, username, email, full_name, phone, role FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetch();
}

// Функция для обновления информации о пользователе
function update_user_info($pdo, $user_id, $email, $full_name, $phone) {
    $stmt = $pdo->prepare("UPDATE users SET email = :email, full_name = :full_name, phone = :phone WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':full_name', $full_name, PDO::PARAM_STR);
    $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Информация обновлена'];
    } else {
        return ['success' => false, 'message' => 'Ошибка при обновлении информации'];
    }
}

// Функция для изменения пароля пользователя
function change_password($pdo, $user_id, $old_password, $new_password) {
    // Получаем текущий пароль пользователя
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch();
    
    // Проверяем старый пароль
    if (!password_verify($old_password, $user['password'])) {
        return ['success' => false, 'message' => 'Неверный текущий пароль'];
    }
    
    // Хешируем новый пароль
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Обновляем пароль в базе данных
    $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Пароль успешно изменен'];
    } else {
        return ['success' => false, 'message' => 'Ошибка при изменении пароля'];
    }
}

// Функция для проверки прав доступа
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['error_message'] = 'Для доступа к этой странице необходимо войти в систему';
        redirect('login.php');
    }
}

// Функция для проверки прав администратора
function require_admin() {
    require_login();
    
    if (!is_admin()) {
        $_SESSION['error_message'] = 'У вас нет прав для доступа к этой странице';
        redirect('index.php');
    }
}
?> 