<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include('db.php');

$user_id = (int)$_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Получение данных пользователя
$user_query = $con->prepare("SELECT * FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user = $user_query->get_result()->fetch_assoc();

// Обработка обновления профиля
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    
    if (!empty($fullname) && !empty($phone) && !empty($email)) {
        $update = $con->prepare("UPDATE users SET fullname = ?, phone = ?, email = ? WHERE id = ?");
        $update->bind_param("sssi", $fullname, $phone, $email, $user_id);
        if ($update->execute()) {
            $success_msg = 'Профиль успешно обновлён!';
            $_SESSION['user_fullname'] = $fullname;
            $user['fullname'] = $fullname;
            $user['phone'] = $phone;
            $user['email'] = $email;
        } else {
            $error_msg = 'Ошибка при обновлении профиля';
        }
    } else {
        $error_msg = 'Пожалуйста, заполните все поля';
    }
}

// Обработка смены пароля
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($user['password'] !== $current_password) {
        $error_msg = 'Текущий пароль введён неверно';
    } elseif (strlen($new_password) < 6) {
        $error_msg = 'Новый пароль должен содержать минимум 6 символов';
    } elseif ($new_password !== $confirm_password) {
        $error_msg = 'Пароли не совпадают';
    } else {
        $update = $con->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->bind_param("si", $new_password, $user_id);
        if ($update->execute()) {
            $success_msg = 'Пароль успешно изменён!';
        } else {
            $error_msg = 'Ошибка при смене пароля';
        }
    }
}

// Обработка отзыва о заявке
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $request_id = (int)$_POST['request_id'];
    $review = trim($_POST['review']);
    $rating = (int)$_POST['rating'];
    
    if (!empty($review)) {
        $update = $con->prepare("UPDATE request SET review = ?, rating = ? WHERE id = ? AND user_id = ?");
        $update->bind_param("siii", $review, $rating, $request_id, $user_id);
        if ($update->execute()) {
            $success_msg = 'Спасибо за ваш отзыв!';
        } else {
            $error_msg = 'Ошибка при сохранении отзыва';
        }
    } else {
        $error_msg = 'Пожалуйста, напишите отзыв';
    }
}

// Получение заявок пользователя
$requests_query = $con->prepare("SELECT * FROM request WHERE user_id = ? ORDER BY date DESC");
$requests_query->bind_param("i", $user_id);
$requests_query->execute();
$requests = $requests_query->get_result();

// Статистика заявок
$stats_query = $con->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Новая' THEN 1 ELSE 0 END) as new,
        SUM(CASE WHEN status = 'Идет обучение' THEN 1 ELSE 0 END) as in_progress,
        SUM(CASE WHEN status = 'Обучение завершено' THEN 1 ELSE 0 END) as completed
    FROM request WHERE user_id = ?
");
$stats_query->bind_param("i", $user_id);
$stats_query->execute();
$stats = $stats_query->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет | Пассажирам.РФ</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(145deg, #f0f4f8 0%, #e2e8f0 100%);
            min-height: 100vh;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: white;
            padding: 30px 24px;
            position: relative;
        }

        .header-content {
            max-width: 1280px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .logo {
            font-size: 24px;
            font-weight: 800;
            text-decoration: none;
            color: white;
        }

        .logo i {
            background: linear-gradient(135deg, #60a5fa, #a855f7);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .header-actions {
            display: flex;
            gap: 12px;
        }

        .btn-logout {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            padding: 10px 24px;
            border-radius: 40px;
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-logout:hover {
            background: rgba(255,255,255,0.2);
        }

        /* Container */
        .container {
            max-width: 1280px;
            margin: -30px auto 60px;
            padding: 0 24px;
        }

        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            border-radius: 28px;
            padding: 32px 40px;
            color: white;
            margin-bottom: 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .welcome-text h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .welcome-text p {
            opacity: 0.9;
        }

        .stats-badge {
            background: rgba(255,255,255,0.2);
            padding: 12px 24px;
            border-radius: 60px;
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 28px;
            font-weight: 800;
        }

        .stat-label {
            font-size: 12px;
            opacity: 0.8;
        }

        /* Grid Layout */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 28px;
        }

        /* Sidebar Cards */
        .profile-card, .password-card {
            background: white;
            border-radius: 28px;
            padding: 28px;
            margin-bottom: 28px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }

        .card-title {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 16px;
            border-bottom: 2px solid #eef2ff;
        }

        .card-title i {
            color: #3b82f6;
            font-size: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            transition: all 0.2s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }

        .btn-primary {
            width: 100%;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(59,130,246,0.3);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #e2e8f0;
            padding: 12px 24px;
            border-radius: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            color: #475569;
        }

        .btn-outline:hover {
            border-color: #3b82f6;
            color: #3b82f6;
        }

        /* Requests Section */
        .requests-section {
            background: white;
            border-radius: 28px;
            padding: 28px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .section-header h2 {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
        }

        .btn-new-request {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            color: white;
            padding: 10px 24px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-new-request:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(59,130,246,0.3);
        }

        /* Request Card */
        .request-card {
            border: 1px solid #eef2f6;
            border-radius: 24px;
            margin-bottom: 20px;
            overflow: hidden;
            transition: all 0.2s;
        }

        .request-card:hover {
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            transform: translateY(-2px);
        }

        .request-header {
            padding: 20px 24px;
            background: #fafbfc;
            border-bottom: 1px solid #eef2f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .request-id {
            font-weight: 700;
            color: #0f172a;
        }

        .request-id i {
            color: #3b82f6;
            margin-right: 8px;
        }

        .status-badge {
            padding: 6px 16px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-new { background: #fef3c7; color: #d97706; }
        .status-progress { background: #dbeafe; color: #2563eb; }
        .status-completed { background: #d1fae5; color: #059669; }

        .request-body {
            padding: 24px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .info-item {
            padding: 12px;
            background: #f8fafc;
            border-radius: 16px;
        }

        .info-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #94a3b8;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .info-value {
            font-size: 15px;
            font-weight: 500;
            color: #1e293b;
        }

        /* Review Section */
        .review-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eef2f6;
        }

        .existing-review {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            padding: 16px 20px;
            border-radius: 20px;
            margin-bottom: 16px;
        }

        .existing-review .stars {
            color: #f59e0b;
            margin-bottom: 8px;
        }

        .existing-review p {
            color: #78350f;
            line-height: 1.5;
        }

        .review-form {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .rating-stars {
            display: flex;
            gap: 8px;
            margin-bottom: 8px;
        }

        .rating-stars i {
            font-size: 24px;
            cursor: pointer;
            color: #cbd5e1;
            transition: all 0.2s;
        }

        .rating-stars i:hover,
        .rating-stars i.active {
            color: #f59e0b;
        }

        .review-form textarea {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e2e8f0;
            border-radius: 20px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            resize: vertical;
            min-height: 80px;
        }

        .review-form textarea:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .btn-submit-review {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: fit-content;
        }

        .btn-submit-review:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(16,185,129,0.3);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 64px;
            color: #cbd5e1;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #1e293b;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #64748b;
        }

        /* Notifications */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            border-radius: 20px;
            font-weight: 500;
            z-index: 1000;
            animation: slideIn 0.3s ease;
        }

        .notification.success {
            background: #10b981;
            color: white;
        }

        .notification.error {
            background: #ef4444;
            color: white;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(100px); }
            to { opacity: 1; transform: translateX(0); }
        }

        /* Toggle for forms */
        .toggle-password-form {
            text-align: center;
            margin-top: 16px;
        }

        .toggle-link {
            color: #3b82f6;
            cursor: pointer;
            font-size: 13px;
            text-decoration: none;
        }

        .password-form-container {
            display: none;
            margin-top: 20px;
        }

        .password-form-container.active {
            display: block;
        }

        @media (max-width: 900px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="header">
    <div class="header-content">
        <a href="index.php" class="logo"><i class="fas fa-bus"></i> Пассажирам.РФ</a>
        <div class="header-actions">
            <a href="index.php" class="btn-logout"><i class="fas fa-home"></i> Главная</a>
            <a href="?logout=1" class="btn-logout" onclick="return confirm('Выйти из аккаунта?')"><i class="fas fa-sign-out-alt"></i> Выход</a>
        </div>
    </div>
</div>

<div class="container">
    <!-- Приветственный баннер -->
    <div class="welcome-banner">
        <div class="welcome-text">
            <h1>👋 Добро пожаловать, <?= htmlspecialchars($user['fullname']) ?>!</h1>
            <p>Управляйте своими заявками и оставляйте отзывы</p>
        </div>
        <div class="stats-badge">
            <div class="stat-item">
                <div class="stat-number"><?= $stats['total'] ?></div>
                <div class="stat-label">Всего заявок</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?= $stats['completed'] ?></div>
                <div class="stat-label">Завершено</div>
            </div>
        </div>
    </div>

    <!-- Уведомления -->
    <?php if ($success_msg): ?>
        <div class="notification success" id="notification">
            <i class="fas fa-check-circle"></i> <?= $success_msg ?>
        </div>
        <script>setTimeout(() => document.getElementById('notification')?.remove(), 3000);</script>
    <?php endif; ?>
    
    <?php if ($error_msg): ?>
        <div class="notification error" id="notification">
            <i class="fas fa-exclamation-triangle"></i> <?= $error_msg ?>
        </div>
        <script>setTimeout(() => document.getElementById('notification')?.remove(), 3000);</script>
    <?php endif; ?>

    <div class="dashboard-grid">
        <!-- Левая колонка - Профиль -->
        <div>
            <div class="profile-card">
                <div class="card-title">
                    <i class="fas fa-user-circle"></i>
                    <span>Личные данные</span>
                </div>
                <form method="POST" action="">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> ФИО</label>
                        <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Телефон</label>
                        <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <button type="submit" name="update_profile" class="btn-primary">
                        <i class="fas fa-save"></i> Сохранить изменения
                    </button>
                </form>
            </div>

            <div class="password-card">
                <div class="card-title">
                    <i class="fas fa-lock"></i>
                    <span>Безопасность</span>
                </div>
                <div class="toggle-password-form">
                    <a href="javascript:void(0)" class="toggle-link" onclick="togglePasswordForm()">
                        <i class="fas fa-key"></i> Сменить пароль
                    </a>
                </div>
                <div id="passwordForm" class="password-form-container">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label>Текущий пароль</label>
                            <input type="password" name="current_password" required>
                        </div>
                        <div class="form-group">
                            <label>Новый пароль</label>
                            <input type="password" name="new_password" minlength="6" required>
                        </div>
                        <div class="form-group">
                            <label>Подтверждение пароля</label>
                            <input type="password" name="confirm_password" required>
                        </div>
                        <button type="submit" name="change_password" class="btn-primary">
                            <i class="fas fa-check"></i> Сменить пароль
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Правая колонка - Заявки -->
        <div class="requests-section">
            <div class="section-header">
                <h2><i class="fas fa-clipboard-list"></i> Мои заявки на обучение</h2>
                <a href="create.php" class="btn-new-request">
                    <i class="fas fa-plus-circle"></i> Новая заявка
                </a>
            </div>

            <?php if ($requests->num_rows == 0): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>У вас пока нет заявок</h3>
                    <p>Запишитесь на курсы, чтобы начать обучение</p>
                    <a href="create.php" style="display: inline-block; margin-top: 20px; color: #3b82f6; font-weight: 600;">Создать заявку →</a>
                </div>
            <?php else: ?>
                <?php while ($request = $requests->fetch_assoc()):
                    $status_class = match($request['status']) {
                        'Новая' => 'status-new',
                        'Идет обучение' => 'status-progress',
                        'Обучение завершено' => 'status-completed',
                        default => 'status-new'
                    };
                    $rating = $request['rating'] ?? 0;
                ?>
                    <div class="request-card">
                        <div class="request-header">
                            <div class="request-id">
                                <i class="far fa-file-alt"></i> Заявка №<?= $request['id'] ?>
                            </div>
                            <span class="status-badge <?= $status_class ?>"><?= htmlspecialchars($request['status']) ?></span>
                        </div>
                        <div class="request-body">
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-label"><i class="far fa-calendar-alt"></i> Дата подачи</div>
                                    <div class="info-value"><?= date('d.m.Y H:i', strtotime($request['date'])) ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label"><i class="fas fa-graduation-cap"></i> Направление</div>
                                    <div class="info-value"><?= htmlspecialchars($request['curses']) ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label"><i class="fas fa-credit-card"></i> Оплата</div>
                                    <div class="info-value"><?= htmlspecialchars($request['payment']) ?></div>
                                </div>
                            </div>

                            <!-- Секция отзывов -->
                            <div class="review-section">
                                <?php if (!empty($request['review'])): ?>
                                    <div class="existing-review">
                                        <div class="stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star<?= $i <= ($rating ?? 0) ? '' : '-o' ?>" style="color: #f59e0b;"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <p><i class="fas fa-quote-left"></i> <?= htmlspecialchars($request['review']) ?></p>
                                    </div>
                                <?php endif; ?>

                                <?php if ($request['status'] == 'Обучение завершено' && empty($request['review'])): ?>
                                    <form method="POST" class="review-form">
                                        <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                        <div class="rating-stars" data-rating="0">
                                            <i class="far fa-star" data-value="1"></i>
                                            <i class="far fa-star" data-value="2"></i>
                                            <i class="far fa-star" data-value="3"></i>
                                            <i class="far fa-star" data-value="4"></i>
                                            <i class="far fa-star" data-value="5"></i>
                                        </div>
                                        <input type="hidden" name="rating" id="rating_<?= $request['id'] ?>" value="0">
                                        <textarea name="review" placeholder="Поделитесь впечатлениями о курсах..."></textarea>
                                        <button type="submit" name="submit_review" class="btn-submit-review">
                                            <i class="fas fa-paper-plane"></i> Оставить отзыв
                                        </button>
                                    </form>
                                <?php elseif ($request['status'] == 'Обучение завершено' && !empty($request['review'])): ?>
                                    <div style="text-align: center; padding: 12px; background: #f1f5f9; border-radius: 16px;">
                                        <i class="fas fa-check-circle" style="color: #10b981;"></i>
                                        <span style="color: #475569; margin-left: 8px;">Спасибо за ваш отзыв!</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Toggle password form
    function togglePasswordForm() {
        const form = document.getElementById('passwordForm');
        form.classList.toggle('active');
    }

    // Rating stars functionality
    document.querySelectorAll('.rating-stars').forEach(container => {
        const stars = container.querySelectorAll('i');
        const ratingInput = container.nextElementSibling;
        
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const value = parseInt(this.dataset.value);
                ratingInput.value = value;
                
                stars.forEach((s, index) => {
                    if (index < value) {
                        s.className = 'fas fa-star';
                    } else {
                        s.className = 'far fa-star';
                    }
                });
            });
            
            star.addEventListener('mouseenter', function() {
                const value = parseInt(this.dataset.value);
                stars.forEach((s, index) => {
                    if (index < value) {
                        s.className = 'fas fa-star';
                    } else {
                        s.className = 'far fa-star';
                    }
                });
            });
            
            container.addEventListener('mouseleave', function() {
                const currentRating = parseInt(ratingInput.value);
                stars.forEach((s, index) => {
                    if (index < currentRating) {
                        s.className = 'fas fa-star';
                    } else {
                        s.className = 'far fa-star';
                    }
                });
            });
        });
    });
</script>
</body>
</html>