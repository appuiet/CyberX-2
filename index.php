<?php
$page_title = 'Главная';
$additional_css = 'section.css';
include 'includes/header.php';
?>

<head>
    <meta name="viewport" content="width=1600, height=1100, initial-scale=1.0">
</head>

<section class="section first-section" style="height: 91.7vh;">
        <div class="section__container">
            <div class="section__info">
                <h1 class="section__title">погнали в cyber<span style="color: red;">x</span>!</h1>
                <p class="section__desc">твой путь в киберспорт</p>
            </div>

            <div class="section__features">
                <div class="feature__card">
                    <h3 class="feature__title">Онлайн-турниры</h3>
                    <p class="feature__text">Играй и побеждай в еженедельных состязаниях</p>
                </div>
                <div class="feature__card">
                    <h3 class="feature__title">Прокачка навыков</h3>
                    <p class="feature__text">Следи за своим ростом и анализируй матчи</p>
                </div>
                <div class="feature__card">
                    <h3 class="feature__title">Комьюнити</h3>
                    <p class="feature__text">Будь частью команды и участвуй в жизни клуба</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section second-section" style="height: 91.7vh;">
        <div class="section__container">
            <div class="section__image-wrapper">
                <img src="/images/arena.png" alt="Киберспортивная арена" class="section__image" style="
    position: relative;
    right: 160px;
">
            </div>

            <div class="section__about">
                <h1 class="section__title section__about-title">о нас</h1>
                <p class="section__about-text">
                    Самая большая киберспортивная АРЕНА в Беларуси, работающая 24/7, в которой слово «сервис» стоит на первом месте! Мы хотим, чтобы вы были для нас не просто гостем, а частью CyberX Community. Для этого мы создали команду, которая в первую очередь заботится о Вас, старается учесть все ваши предпочтения и пожелания.
                </p>
                <ul class="section__about-list">
                    <li class="section__about-item">Просторные VIP с личными ключ-картами;</li>
                    <li class="section__about-item">Комфортная lounge-зона с PS5 и бар;</li>
                    <li class="section__about-item">Игровые автосимуляторы;</li>
                    <li class="section__about-item">Еженедельные розыгрыши;</li>
                    <li class="section__about-item">Возможность стать частью киберспортивной команды.</li>
                </ul>
                <p class="section__partners-title">Наши партнёры</p>
                <div class="section__partners-images">
                    <img src="/images/partner-gorilla-icon.jpg" alt="Gorilla logo" class="section__partner-image">
                    <img src="/images/partner-bet-icon.jpg" alt="Bettera logo" class="section__partner-image">
                    <img src="/images/partner-dinamo-icon.png" alt="Dinamo logo" class="section__partner-image">
                </div>
            </div>
        </div>
    </section>

    <section class="section third-section">
        <div class="section__container">
            <div class="third-section__header">
            <h2 class="third-section__title">Наши основные игры</h2>
            <p class="third-section__desc">
                Только лицензионные игры с возможностью подключения вашего аккаунта и участия в турнирах на нашей Арене
            </p>
            </div>
            <div class="third-section__games">
                <div class="game-card">
                    <img src="/images/game-gta-5-image.jpeg" alt="GTA 5" class="game-card__image">
                    <span class="game-card__name">GTA V</span>
                </div>
                <div class="game-card">
                    <img src="/images/game-pubg-image.jpg" alt="PUBG" class="game-card__image">
                    <span class="game-card__name">PUBG</span>
                </div>
                <div class="game-card">
                    <img src="/images/game-dota-2-image.jpg" alt="Dota 2" class="game-card__image">
                    <span class="game-card__name">Dota 2</span>
                </div>
                <div class="game-card">
                    <img src="/images/game-fortnite-image.jpg" alt="Fortnite" class="game-card__image">
                    <span class="game-card__name">Fortnite</span>
                </div>
                <div class="game-card">
                    <img src="/images/game-apex-image.jpg" alt="Apex Legends" class="game-card__image" >
                    <span class="game-card__name">Apex Legends</span>
                </div>
            </div>
        </div>
    </section>
<?php
            include 'includes/footer.php';
?>
</div>