<?php
$page_title = 'Тест Сайдбара';
include 'includes/header.php';
?>

<div style="padding: 20px;">
    <h1>Тестовая страница для проверки работы сайдбара</h1>
    <p>Нажмите на кнопку в левом верхнем углу для проверки работы toggle-sidebar.</p>
    
    <div style="margin-top: 20px;">
        <button id="testToggle" class="btn">Тестовое переключение сайдбара</button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const testButton = document.getElementById('testToggle');
    if (testButton) {
        testButton.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-collapsed');
            console.log('Тестовая кнопка нажата, состояние:', document.body.classList.contains('sidebar-collapsed'));
        });
    }
});
</script>

<?php
include 'includes/footer.php';
?> 