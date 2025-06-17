<div style="display: flex; flex-direction: column; min-height: 100vh;">
<?php
$page_title = 'Клубы';
$additional_css = 'style.css';
include 'includes/header.php';
?>
<head>
<meta name="viewport" content="width=1600, height=1100, initial-scale=1.0">
</head>

<div class="main-content">
    <!-- Блок для изображения -->
    <div class="image-container">
        <img src="IMG/clubs.png" alt="Описание изображения" class="centered-image">
    </div>
    <div class="content-row">
        <div class="content-block">
            <div class="large-text">30</div>
            <div class="small-text">машин</div>
        </div>
        <div class="content-block">
            <div class="large-text">Мощные</div>
            <div class="small-text">компьютеры</div>
        </div>
        <div class="content-block">
            <div class="large-text">Комфортные</div>
            <div class="small-text">кресла</div>
        </div>
    </div>

    <!-- Новый блок с текстом ниже -->
    <div class="content-row">
        <div class="content-block">
            <div class="large-text">Большой</div>
            <div class="small-text">выбор еды и напитков</div>
        </div>
        <div class="content-block">
            <div class="large-text">Удобная</div>
            <div class="small-text">локация</div>
        </div>
        <div class="content-block">
            <div class="large-text">Круглосуточно</div>
            <div class="small-text">время работы</div>
        </div>
    </div>

    <!-- Простой белый фон -->
    <div class="white-background">
        <h2 class="equipment-title">ОБОРУДОВАНИЕ</h2>
        <div class="image-row">
            <div class="image-container">
                <h3 class="image-label">Стандарт</h3>
                <img src="IMG/standart.png" alt="Описание первого изображения" class="equipment-image">
            </div>
            <div class="image-container">
                <h3 class="image-label">VIP</h3>
                <img src="IMG/vip.png" alt="Описание второго изображения" class="equipment-image">
            </div>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>
</div> 