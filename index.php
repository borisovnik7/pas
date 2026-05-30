<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Пассажирам.РФ – обучение водителей</title>
    <link href="https://fonts.googleapis.com/css2?family=PT+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        /* дополнительные стили для главной */
        .hero {
            background: linear-gradient(135deg, #e9ecef, #ffffff);
            padding: 60px 0;
        }
        .hero-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
        }
        .stat-number { font-size: 32px; font-weight: 700; color: #007bff; }
        .directions-grid, .advantages-grid, .reviews-grid {
            display: grid;
            gap: 30px;
            margin-top: 40px;
        }
        .directions-grid { grid-template-columns: repeat(3,1fr); }
        .advantages-grid { grid-template-columns: repeat(4,1fr); }
        .reviews-grid { grid-template-columns: repeat(3,1fr); }
        .direction-card, .advantage-item, .review-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        @media (max-width: 768px) {
            .directions-grid, .advantages-grid, .reviews-grid { grid-template-columns: 1fr; }
            .hero-grid { grid-template-columns: 1fr; text-align: center; }
        }
    </style>
</head>
<body>
<header class="header">
    <div class="container header-flex">
        <a href="index.php" class="logo">
            <div class="logo-icon">🚌</div>
            <div><div class="logo-text"><span>Пассажирам.</span>РФ</div><div class="logo-sub">Профессиональное обучение</div></div>
        </a>
        <div class="nav-links">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['is_admin']): ?>
                    <a href="admin.php">🔧 Управление</a>
                <?php else: ?>
                    <a href="profile.php">👤 Личный кабинет</a>
                    <a href="request.php" class="btn-primary">📝 Новая заявка</a>
                <?php endif; ?>
                <a href="logout.php">🚪 Выход</a>
            <?php else: ?>
                <a href="login.php">🔐 Вход</a>
                <a href="register.php" class="btn-primary">📝 Регистрация</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<section class="hero">
    <div class="container hero-grid">
        <div><h1>Стань профессионалом<br>пассажирских перевозок <span style="color:#007bff">с нами</span></h1>
        <p>Лицензированное обучение водителей автобусов, электробусов и трамваев. Опытные инструкторы, современный автопарк, трудоустройство.</p>
        <a href="register.php" class="btn-primary">Начать обучение →</a>
        <div class="hero-stats" style="display:flex; gap:30px; margin-top:30px;">
            <div><div class="stat-number">5000+</div><div>Выпускников</div></div>
            <div><div class="stat-number">98%</div><div>Трудоустройство</div></div>
            <div><div class="stat-number">15 лет</div><div>На рынке</div></div>
        </div></div>
        <div><img src="images/hero-bus.png" alt="Bus" style="max-width:100%"></div>
    </div>
</section>

<!-- Слайдер (авто 3 сек) -->
<section class="slider-section">
    <div class="container"><h2 style="text-align:center">Фотогалерея обучения</h2><p style="text-align:center">Реальные кадры с занятий</p></div>
    <div class="slider-wrapper">
        <div class="slider">
            <div class="slide-item active"><img src="images/374e0e8da34a4f029351687cb429f1c1.jpg"><div class="slide-caption"><h3>Водитель автобуса</h3><p>Категория D</p></div></div>
            <div class="slide-item"><img src="images/2sz0dDzQvfUUVjGc02sM.jpg"><div class="slide-caption"><h3>Электробусы</h3><p>Экологичный транспорт</p></div></div>
            <div class="slide-item"><img src="images/5ea09b2e31416396857a41b46232b058.png"><div class="slide-caption"><h3>Трамваи</h3><p>Практика на маршрутах</p></div></div>
            <div class="slide-item"><img src="images/aCc1IgfVuXD8tscUiKjY_UQGo4Grof5U.jpg"><div class="slide-caption"><h3>Современные методики</h3><p>Теория и практика</p></div></div>
            <button class="slider-btn btn-prev" onclick="changeSlide(-1)">❮</button>
            <button class="slider-btn btn-next" onclick="changeSlide(1)">❯</button>
        </div>
        <div class="slider-dots" id="sliderDots"></div>
    </div>
</section>

<script>
    let slides = document.querySelectorAll('.slide-item');
    let currentSlide = 0, autoInterval;
    let dotsContainer = document.getElementById('sliderDots');
    if (dotsContainer && slides.length) {
        slides.forEach((_,i)=>{let d=document.createElement('span');d.classList.add('dot');if(i===0)d.classList.add('active');d.onclick=()=>goToSlide(i);dotsContainer.appendChild(d);});
    }
    let dots = document.querySelectorAll('.dot');
    function showSlide(index) {
        slides.forEach(s=>s.classList.remove('active'));
        dots.forEach(d=>d.classList.remove('active'));
        currentSlide = (index + slides.length) % slides.length;
        slides[currentSlide].classList.add('active');
        if(dots[currentSlide]) dots[currentSlide].classList.add('active');
    }
    function changeSlide(direction) { currentSlide += direction; showSlide(currentSlide); resetAutoSlide(); }
    function goToSlide(index) { currentSlide = index; showSlide(currentSlide); resetAutoSlide(); }
    function autoSlide() { currentSlide++; showSlide(currentSlide); }
    function resetAutoSlide() { clearInterval(autoInterval); autoInterval = setInterval(autoSlide, 3000); }
    if (slides.length) { autoInterval = setInterval(autoSlide, 3000); }
    document.querySelector('.slider-wrapper')?.addEventListener('mouseenter',()=>clearInterval(autoInterval));
    document.querySelector('.slider-wrapper')?.addEventListener('mouseleave',()=>{autoInterval=setInterval(autoSlide,3000);});
</script>

<!-- Прочие секции (направления, преимущества, отзывы) аналогично, но для краткости опущены, можно взять из ранее предложенного кода -->
</body>
</html>