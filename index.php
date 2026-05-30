<?php
session_start();

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$is_admin = isset($_SESSION['admin']) && $_SESSION['admin'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Пассажирам.РФ — обучение водителей пассажирских перевозок</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            color: #0f172a;
        }

        /* Навбар */
        .navbar {
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .logo {
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .nav-btn {
            padding: 10px 22px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .nav-btn-primary {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            color: white;
        }

        .nav-btn-outline {
            border: 2px solid #e2e8f0;
            color: #475569;
        }

        .nav-btn-outline:hover {
            border-color: #3b82f6;
            color: #3b82f6;
        }

        .nav-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(59,130,246,0.3);
        }

        /* Hero секция */
        .hero {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: white;
            padding: 60px 24px;
            text-align: center;
        }

        .hero h1 {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 20px;
        }

        .hero p {
            font-size: 18px;
            opacity: 0.8;
            max-width: 600px;
            margin: 0 auto 32px;
        }

        .hero-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .hero-btn {
            padding: 14px 32px;
            border-radius: 60px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.2s;
        }

        .hero-btn-primary {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            color: white;
        }

        .hero-btn-outline {
            border: 2px solid white;
            color: white;
        }

        .hero-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(59,130,246,0.4);
        }

        .hero-btn-outline:hover {
            background: rgba(255,255,255,0.1);
        }

        /* ========== СЛАЙДЕР ========== */
        .slider-section {
            max-width: 1200px;
            margin: 60px auto 0;
            padding: 0 24px;
        }

        .slider-container {
            position: relative;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 20px 40px -12px rgba(0,0,0,0.2);
            background: #0f172a;
        }

        .slider-wrapper {
            position: relative;
            overflow: hidden;
        }

        .slides {
            display: flex;
            transition: transform 0.5s ease-in-out;
        }

        .slide {
            min-width: 100%;
            position: relative;
        }

        .slide img {
            width: 100%;
            height: 480px;
            object-fit: cover;
            display: block;
        }

        .slide-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.85), transparent);
            padding: 50px 40px 30px;
            color: white;
        }

        .slide-caption h3 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .slide-caption p {
            font-size: 16px;
            opacity: 0.9;
        }

        /* Кнопки слайдера */
        .slider-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border: none;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            cursor: pointer;
            color: white;
            font-size: 20px;
            transition: all 0.2s;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .slider-btn:hover {
            background: rgba(255,255,255,0.4);
            transform: translateY(-50%) scale(1.05);
        }

        .slider-btn.prev { left: 20px; }
        .slider-btn.next { right: 20px; }

        /* Точки (индикаторы) */
        .dots-container {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 20px;
            padding: 16px 0;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #cbd5e1;
            cursor: pointer;
            transition: all 0.2s;
        }

        .dot.active {
            width: 28px;
            border-radius: 10px;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
        }

        /* Преимущества */
        .features {
            max-width: 1280px;
            margin: 80px auto;
            padding: 0 24px;
        }

        .features h2 {
            text-align: center;
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 48px;
            background: linear-gradient(135deg, #0f172a, #3b82f6);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 32px;
        }

        .feature-card {
            background: white;
            padding: 32px 28px;
            border-radius: 28px;
            text-align: center;
            transition: all 0.3s;
            border: 1px solid #e2e8f0;
        }

        .feature-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 30px -12px rgba(0,0,0,0.1);
            border-color: #3b82f6;
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #eef2ff, #e0e7ff);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
        }

        .feature-card h3 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .feature-card p {
            color: #64748b;
            line-height: 1.6;
        }

        /* Футер */
        .footer {
            background: #0f172a;
            color: #94a3b8;
            text-align: center;
            padding: 40px 24px;
            margin-top: 60px;
        }

        @media (max-width: 768px) {
            .hero h1 { font-size: 32px; }
            .slide img { height: 300px; }
            .slide-caption { padding: 30px 20px 20px; }
            .slide-caption h3 { font-size: 20px; }
            .features h2 { font-size: 28px; }
            .slider-btn { width: 36px; height: 36px; font-size: 14px; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <a href="index.php" class="logo"><i class="fas fa-bus"></i> Пассажирам.РФ</a>
        <div class="nav-links">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="login.php" class="nav-btn nav-btn-outline"><i class="fas fa-sign-in-alt"></i> Войти</a>
                <a href="register.php" class="nav-btn nav-btn-primary"><i class="fas fa-user-plus"></i> Регистрация</a>
            <?php elseif ($is_admin): ?>
                <a href="admin.php" class="nav-btn nav-btn-primary"><i class="fas fa-chalkboard-user"></i> Админ-панель</a>
                <a href="?logout=1" class="nav-btn nav-btn-outline"><i class="fas fa-sign-out-alt"></i> Выход</a>
            <?php elseif (isset($_SESSION['user_id'])): ?>
                <a href="profile.php" class="nav-btn nav-btn-outline"><i class="fas fa-user"></i> Личный кабинет</a>
                <a href="create.php" class="nav-btn nav-btn-primary"><i class="fas fa-plus-circle"></i> Новая заявка</a>
                <a href="?logout=1" class="nav-btn nav-btn-outline"><i class="fas fa-sign-out-alt"></i> Выход</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<section class="hero">
    <h1>Стань водителем<br>пассажирского транспорта</h1>
    <p>Профессиональное обучение на автобус, электробус и трамвай с выдачей свидетельства</p>
    <div class="hero-buttons">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="create.php" class="hero-btn hero-btn-primary"><i class="fas fa-pen-alt"></i> Записаться на курсы</a>
        <?php else: ?>
            <a href="register.php" class="hero-btn hero-btn-primary"><i class="fas fa-user-plus"></i> Начать обучение</a>
            <a href="login.php" class="hero-btn hero-btn-outline"><i class="fas fa-sign-in-alt"></i> Уже есть аккаунт</a>
        <?php endif; ?>
    </div>
</section>

<!-- ========== СЛАЙДЕР ========== -->
<div class="slider-section">
    <div class="slider-container">
        <div class="slider-wrapper">
            <div class="slides" id="slides">
                <!-- СЛАЙД 1 - ЗДЕСЬ ВЫ МЕНЯЕТЕ НАЗВАНИЕ ФАЙЛА -->
                <div class="slide">
                    <img src="images/5ea09b2e31416396857a41b46232b058.png" alt="Обучение на автобус">
                    <div class="slide-caption">
                        <h3> Обучение на автобус</h3>
                        <p>Категория D — профессиональные водители пассажирских автобусов</p>
                    </div>
                </div>
                <!-- СЛАЙД 2 - ЗДЕСЬ ВЫ МЕНЯЕТЕ НАЗВАНИЕ ФАЙЛА -->
                <div class="slide">
                    <img src="images/b7d2d780c0cca44401c954ed5.jpg" alt="Обучение на электробус">
                    <div class="slide-caption">
                        <h3> Обучение на электробус</h3>
                        <p>Экологичный транспорт будущего — освойте новую профессию</p>
                    </div>
                </div>
                <!-- СЛАЙД 3 - ЗДЕСЬ ВЫ МЕНЯЕТЕ НАЗВАНИЕ ФАЙЛА -->
                <div class="slide">
                    <img src="images/aCc1IgfVuXD8tscUiKjY_UQGo4Grof5U.jpg" alt="Обучение на трамвай">
                    <div class="slide-caption">
                        <h3> Обучение на трамвай</h3>
                        <p>Вождение трамвая — стабильная и востребованная профессия</p>
                    </div>
                </div>
                <!-- СЛАЙД 4 - ЗДЕСЬ ВЫ МЕНЯЕТЕ НАЗВАНИЕ ФАЙЛА (опционально) -->
                <div class="slide">
                    <img src="images/374e0e8da34a4f029351687cb429f1c1.jpg" alt="Теория и практика">
                    <div class="slide-caption">
                        <h3> Теория + Практика</h3>
                        <p>Современные учебные классы и опытные инструкторы</p>
                    </div>
                </div>
            </div>
        </div>
        <button class="slider-btn prev" id="prevBtn">❮</button>
        <button class="slider-btn next" id="nextBtn">❯</button>
    </div>
    <div class="dots-container" id="dotsContainer"></div>
</div>

<section class="features">
    <h2>Почему выбирают нас?</h2>
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon"><i class="fas fa-chalkboard-user" style="color: #3b82f6;"></i></div>
            <h3>Опытные инструкторы</h3>
            <p>Преподаватели с многолетним стажем работы в пассажирских перевозках</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon"><i class="fas fa-car-side" style="color: #8b5cf6;"></i></div>
            <h3>Современный автопарк</h3>
            <p>Обучение на новых автобусах, электробусах и трамваях</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon"><i class="fas fa-file-alt" style="color: #10b981;"></i></div>
            <h3>Официальные документы</h3>
            <p>Свидетельство установленного образца после окончания курсов</p>
        </div>
    </div>
</section>

<footer class="footer">
    <p>© 2026 Пассажирам.РФ — обучение водителей пассажирского транспорта</p>
</footer>

<script>
    // ========== СЛАЙДЕР ==========
    const slides = document.getElementById('slides');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const dotsContainer = document.getElementById('dotsContainer');
    
    if (slides) {
        const slideCount = document.querySelectorAll('.slide').length;
        let currentIndex = 0;
        let autoInterval;
        let isDragging = false;
        let startPos = 0;
        let currentTranslate = 0;

        // Создание точек
        function createDots() {
            dotsContainer.innerHTML = '';
            for (let i = 0; i < slideCount; i++) {
                const dot = document.createElement('div');
                dot.classList.add('dot');
                if (i === currentIndex) dot.classList.add('active');
                dot.addEventListener('click', () => goToSlide(i));
                dotsContainer.appendChild(dot);
            }
        }

        // Обновление активной точки
        function updateDots() {
            document.querySelectorAll('.dot').forEach((dot, i) => {
                dot.classList.toggle('active', i === currentIndex);
            });
        }

        // Переход к слайду
        function goToSlide(index) {
            currentIndex = index;
            slides.style.transform = `translateX(-${currentIndex * 100}%)`;
            updateDots();
            resetAutoSlide();
        }

        // Следующий слайд
        function nextSlide() {
            currentIndex = (currentIndex + 1) % slideCount;
            goToSlide(currentIndex);
        }

        // Предыдущий слайд
        function prevSlide() {
            currentIndex = (currentIndex - 1 + slideCount) % slideCount;
            goToSlide(currentIndex);
        }

        // Автоматическая смена
        function startAutoSlide() {
            autoInterval = setInterval(nextSlide, 4000);
        }

        function resetAutoSlide() {
            clearInterval(autoInterval);
            startAutoSlide();
        }

        // Обработчики событий
        if (prevBtn) prevBtn.addEventListener('click', () => { prevSlide(); resetAutoSlide(); });
        if (nextBtn) nextBtn.addEventListener('click', () => { nextSlide(); resetAutoSlide(); });

        // Пауза при наведении на слайдер
        const sliderContainer = document.querySelector('.slider-container');
        if (sliderContainer) {
            sliderContainer.addEventListener('mouseenter', () => clearInterval(autoInterval));
            sliderContainer.addEventListener('mouseleave', startAutoSlide);
        }

        // Инициализация
        createDots();
        startAutoSlide();
    }
</script>
</body>
</html>