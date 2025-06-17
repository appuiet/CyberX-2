<?php
// Начинаем сессию и устанавливаем заголовки до вывода данных
session_start();

$page_title = 'Обратная связь';
$additional_css = 'feedback.css';

// Обработка формы обратной связи
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Подключаем необходимые файлы
    require_once 'config/db_connect.php';
    require_once 'includes/functions.php';
    require_once 'includes/auth.php';
    require_once 'includes/cart.php';
    
    $name = clean_input($_POST['name']);
    $email = clean_input($_POST['email']);
    $subject = clean_input($_POST['subject']);
    $message = clean_input($_POST['message']);
    $user_id = is_logged_in() ? $_SESSION['user_id'] : null;
    
    // Проверяем, заполнены ли все обязательные поля
    if (empty($name) || empty($email) || empty($message)) {
        $_SESSION['error_message'] = 'Заполните все обязательные поля';
    }
    // Проверяем корректность email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = 'Введите корректный email';
    }
    else {
        // Отправляем обратную связь
        $result = submit_feedback($pdo, $user_id, $name, $email, $subject, $message);
        
        if ($result) {
            $_SESSION['success_message'] = 'Ваше сообщение успешно отправлено! Мы свяжемся с вами в ближайшее время.';
            // Перенаправляем на ту же страницу, чтобы избежать повторной отправки формы
            header('Location: feedback.php');
            exit;
        } else {
            $_SESSION['error_message'] = 'Произошла ошибка при отправке сообщения. Пожалуйста, попробуйте позже.';
        }
    }
    
    // Если произошла ошибка, перенаправляем на страницу обратной связи
    header('Location: feedback.php');
    exit;
}

// Включаем заголовок после всех возможных перенаправлений
include 'includes/header.php';
?>

<head>
<meta name="viewport" content="width=1600, height=1100, initial-scale=1.0">
</head>


<div class="feedback-page">

<div class="main-content">
    <div class="feedback-intro">
        <h1>Чтобы с нами связаться и узнать более подробную информацию, вы можете использовать следующие способы:</h1>
        <p>Перейдите по любой из соцсетей и задайте свой вопрос или позвоните нам!</p>
        <p>Так же для бронирования PC или целого зала для турнира, обращайтесь по номеру телефона!</p>
    </div>
    
    <div class="contact-info">
        <img src="IMG/cont.png" alt="Телефон" class="footer-photo">
        <span class="photo-number">+375292736363</span>
    </div>
    
    <div class="social-links">
        <div class="social-item">
            <a href="https://vk.com/cyberx_minsk" target="_blank">
                <img src="IMG/vka.png" alt="ВКонтакте" class="footer-photo">
            </a>
            <span class="photo-number">Вконтакте</span>
        </div>
        
        <div class="social-item">
            <a href="https://www.instagram.com/cyberx_minsk/" target="_blank">
                <img src="IMG/INST.png" alt="Instagram" class="footer-photo">
            </a>
            <span class="photo-number">INSTAGRAM</span>
        </div>
        
        <div class="social-item">
            <a href="https://www.tiktok.com/@cyberxminsk" target="_blank">
                <img src="IMG/TIKTOKS.png" alt="TikTok" class="footer-photo">
            </a>
            <span class="photo-number">TikTok</span>
        </div>
    </div>
    
    <!-- Форма обратной связи -->
    <div class="feedback-form-container">
        <h2>Отправить сообщение</h2>
        <form action="feedback.php" method="post" class="feedback-form">
            <div class="form-group">
                <label for="name"><i class="fas fa-user"></i> Ваше имя: *</label>
                <input type="text" id="name" name="name" placeholder="Введите ваше имя" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : (is_logged_in() ? htmlspecialchars($_SESSION['username']) : ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email: *</label>
                <input type="email" id="email" name="email" placeholder="Введите ваш email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="subject"><i class="fas fa-heading"></i> Тема:</label>
                <input type="text" id="subject" name="subject" placeholder="Тема сообщения" value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="message"><i class="fas fa-comment"></i> Сообщение: *</label>
                <textarea id="message" name="message" placeholder="Введите ваше сообщение" rows="6" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
            </div>
            
            <button type="submit" class="auth-button">Отправить <i class="fas fa-paper-plane"></i></button>
        </form>
    </div>
</div>

<?php
include 'includes/footer.php';
?>
</div> 