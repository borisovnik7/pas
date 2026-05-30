<?php
session_start();
if (!isset($_SESSION['user_id'])) die('Чтобы записаться на курсы, надо войти в аккаунт.');

$success = false;
$error = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $review = $_POST['review'];
    $date = $_POST['date'];
    $venue = $_POST['venue'];
    $payment = $_POST['payment'];
    $status = 'Новая';
    
    include('db.php');
    
    $user_id = (int)$_SESSION['user_id'];
    $review = $con->real_escape_string($review);
    $venue = $con->real_escape_string($venue);
    $payment = $con->real_escape_string($payment);
    
    $query = $con->query("INSERT INTO request (review, date, curses, payment, user_id, status) 
                          VALUES ('$review', '$date', '$venue', '$payment', '$user_id', '$status')");
    
    if (!$query) {
        $error = true;
        $error_msg = 'Ошибка: ' . $con->error;
    } else {
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Запись на курсы | Пассажирам.РФ</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(145deg, #f0f4f8 0%, #e2e8f0 100%);
            min-height: 100vh;
        }

        .hero-section {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: white;
            padding: 60px 20px;
            text-align: center;
        }

        .hero-section h1 {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 16px;
        }

        .hero-section p {
            font-size: 18px;
            opacity: 0.8;
            max-width: 600px;
            margin: 0 auto;
        }

        .container {
            max-width: 700px;
            margin: -40px auto 60px;
            padding: 0 20px;
        }

        .form-card {
            background: white;
            border-radius: 32px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            overflow: hidden;
        }

        .form-header {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            padding: 28px 32px;
            color: white;
        }

        .form-header h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .form-header p {
            opacity: 0.9;
            font-size: 14px;
        }

        form {
            padding: 32px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group label i {
            color: #3b82f6;
            width: 24px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            transition: all 0.2s;
            background: #fafbfc;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 4px rgba(59,130,246,0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(59,130,246,0.4);
        }

        .nav-links {
            display: flex;
            gap: 16px;
            justify-content: center;
            margin-bottom: 24px;
        }

        .nav-link {
            padding: 10px 24px;
            background: white;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 500;
            color: #1e293b;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .nav-link:hover {
            background: #3b82f6;
            color: white;
            transform: translateY(-2px);
        }

        .success-message {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #065f46;
            padding: 24px;
            border-radius: 20px;
            text-align: center;
        }

        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 16px 20px;
            border-radius: 16px;
            margin-bottom: 24px;
            text-align: center;
        }

        @media (max-width: 640px) {
            .hero-section h1 { font-size: 32px; }
            form { padding: 24px; }
        }
    </style>
</head>
<body>
<div class="hero-section">
    <h1><i class="fas fa-graduation-cap"></i> Запись на курсы</h1>
    <p>Станьте профессиональным водителем пассажирского транспорта</p>
</div>

<div class="container">
    <div class="nav-links">
        <a href="index.php" class="nav-link"><i class="fas fa-home"></i> Главная</a>
        <a href="history.php" class="nav-link"><i class="fas fa-history"></i> Мои заявки</a>
    </div>

    <div class="form-card">
        <div class="form-header">
            <h2><i class="fas fa-clipboard-list"></i> Новая заявка</h2>
            <p>Заполните форму, и наш менеджер свяжется с вами</p>
        </div>

        <?php if ($success): ?>
            <div style="padding: 32px;">
                <div class="success-message">
                    <i class="fas fa-check-circle" style="font-size: 48px; margin-bottom: 16px; display: inline-block;"></i>
                    <h3>Заявка успешно отправлена!</h3>
                    <p style="margin-top: 12px;">Наш менеджер свяжется с вами в ближайшее время</p>
                    <a href="history.php" style="display: inline-block; margin-top: 20px; color: #065f46; font-weight: 600;">📋 Перейти к моим заявкам →</a>
                </div>
            </div>
        <?php elseif ($error): ?>
            <div style="padding: 32px;">
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_msg); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST" action="">
            <div class="form-group">
                <label><i class="fas fa-bus"></i> Направление обучения</label>
                <select name="venue" required>
                    <option value="Водитель автобуса">🚌 Водитель автобуса (категория D)</option>
                    <option value="Водитель электробуса">⚡ Водитель электробуса</option>
                    <option value="Водитель трамвая">🚋 Водитель трамвая</option>
                </select>
            </div>

            <div class="form-group">
                <label><i class="fas fa-calendar-alt"></i> Желаемая дата начала</label>
                <input type="datetime-local" name="date" required>
            </div>

            <div class="form-group">
                <label><i class="fas fa-credit-card"></i> Форма оплаты</label>
                <select name="payment" required>
                    <option value="наличные">💵 Наличные</option>
                    <option value="перевод">🏦 Безналичный перевод</option>
                    <option value="карта">💳 Банковской картой</option>
                </select>
            </div>

            <div class="form-group">
                <label><i class="fas fa-comment-dots"></i> Дополнительная информация</label>
                <textarea name="review" placeholder="Укажите наличие водительского стажа, желаемый график обучения..."></textarea>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-paper-plane"></i> Отправить заявку
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>