<?php
session_start();
if(!isset($_SESSION['user_id'])) die('Чтобы посмотреть историю заявок, надо войти в аккаунт.');
include('db.php');

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['review'])) {
    $review = $con->real_escape_string($_POST['review']);
    $user_id = (int)$_SESSION['user_id'];
    $request_id = (int)$_POST['request_id'];
    $con->query("UPDATE request SET review='$review' WHERE id='$request_id' AND user_id='$user_id'");
    $success_msg = true;
}

$user_id = (int)$_SESSION['user_id'];
$query = $con->query("SELECT * FROM request WHERE user_id='$user_id' ORDER BY date DESC");
if(!$query) die('query error: ' . $con->error); 
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мои заявки | Пассажирам.РФ</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(145deg, #f0f4f8 0%, #e2e8f0 100%);
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }

        .header h1 {
            font-size: 40px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .container {
            max-width: 900px;
            margin: -30px auto 60px;
            padding: 0 20px;
        }

        .stats-banner {
            background: white;
            border-radius: 24px;
            padding: 20px 28px;
            margin-bottom: 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .stats-banner .count {
            font-size: 32px;
            font-weight: 800;
            color: #3b82f6;
        }

        .btn-home {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            color: white;
            padding: 12px 28px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(59,130,246,0.3);
        }

        .request-card {
            background: white;
            border-radius: 24px;
            margin-bottom: 20px;
            overflow: hidden;
            transition: all 0.2s;
            border: 1px solid #e2e8f0;
        }

        .request-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -8px rgba(0,0,0,0.1);
        }

        .card-header {
            padding: 20px 24px;
            background: #fafbfc;
            border-bottom: 1px solid #eef2f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .request-number {
            font-weight: 700;
            font-size: 18px;
            color: #0f172a;
        }

        .request-number i {
            color: #3b82f6;
            margin-right: 8px;
        }

        .status-badge {
            padding: 6px 16px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
        }

        .status-new { background: #fef3c7; color: #d97706; }
        .status-processing { background: #dbeafe; color: #2563eb; }
        .status-completed { background: #d1fae5; color: #059669; }

        .card-body {
            padding: 24px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-bottom: 20px;
        }

        .info-item {
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .info-label {
            font-size: 12px;
            color: #94a3b8;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 15px;
            font-weight: 500;
            color: #1e293b;
        }

        .review-section {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
        }

        .review-text {
            background: #f8fafc;
            padding: 14px 18px;
            border-radius: 16px;
            margin-bottom: 12px;
            border-left: 4px solid #3b82f6;
        }

        .review-form {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .review-form input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
        }

        .review-form input:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .review-form button {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .review-form button:hover {
            transform: translateY(-1px);
        }

        .create-btn {
            text-align: center;
            margin-top: 40px;
        }

        .create-btn a {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            color: white;
            padding: 14px 36px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s;
        }

        .create-btn a:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(59,130,246,0.4);
        }

        .empty-state {
            background: white;
            border-radius: 24px;
            padding: 60px 20px;
            text-align: center;
        }

        .empty-state i {
            font-size: 64px;
            color: #cbd5e1;
            margin-bottom: 20px;
        }

        .success-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #10b981;
            color: white;
            padding: 14px 24px;
            border-radius: 14px;
            font-weight: 500;
            animation: slideIn 0.3s ease;
            z-index: 1000;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(100px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @media (max-width: 640px) {
            .info-grid { grid-template-columns: 1fr; }
            .review-form { flex-direction: column; }
        }
    </style>
</head>
<body>

<?php if(isset($success_msg)): ?>
    <div class="success-toast"><i class="fas fa-check-circle"></i> Отзыв успешно сохранён!</div>
    <script>setTimeout(() => document.querySelector('.success-toast')?.remove(), 3000);</script>
<?php endif; ?>

<div class="header">
    <h1><i class="fas fa-history"></i> Мои заявки</h1>
    <p>История ваших обращений на обучение</p>
</div>

<div class="container">
    <div class="stats-banner">
        <div>
            <i class="fas fa-clipboard-list" style="color: #3b82f6; font-size: 24px;"></i>
            <span style="margin-left: 12px; font-weight: 500;">Всего заявок:</span>
            <span class="count"><?= $query->num_rows ?></span>
        </div>
        <a href="index.php" class="btn-home"><i class="fas fa-home"></i> На главную</a>
    </div>

    <?php if($query->num_rows == 0): ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3 style="margin-bottom: 8px;">У вас пока нет заявок</h3>
            <p style="color: #64748b;">Запишитесь на курсы, чтобы начать обучение</p>
            <div class="create-btn" style="margin-top: 24px;">
                <a href="create.php"><i class="fas fa-plus-circle"></i> Создать заявку</a>
            </div>
        </div>
    <?php else: ?>
        <?php while($request = $query->fetch_assoc()): 
            $status_class = match($request['status']) {
                'Новая' => 'status-new',
                'В обработке' => 'status-processing',
                'Завершено', 'Обучение завершено' => 'status-completed',
                default => 'status-new'
            };
        ?>
            <div class="request-card">
                <div class="card-header">
                    <div class="request-number">
                        <i class="fas fa-hashtag"></i> Заявка №<?= $request['id'] ?>
                    </div>
                    <span class="status-badge <?= $status_class ?>"><?= htmlspecialchars($request['status']) ?></span>
                </div>
                <div class="card-body">
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

                    <div class="review-section">
                        <?php if(!empty($request['review'])): ?>
                            <div class="review-text">
                                <i class="fas fa-star" style="color: #f59e0b;"></i>
                                <strong>Ваш отзыв:</strong> <?= htmlspecialchars($request['review']) ?>
                            </div>
                        <?php endif; ?>

                        <?php if($request['status'] === 'Обучение завершено' || $request['status'] === 'Завершено'): ?>
                            <form method="POST" class="review-form">
                                <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                <input type="text" name="review" placeholder="✍️ Оставьте отзыв о качестве обучения..." value="<?= htmlspecialchars($request['review'] ?? '') ?>">
                                <button type="submit"><i class="fas fa-paper-plane"></i> Отправить</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>

        <div class="create-btn">
            <a href="create.php"><i class="fas fa-plus-circle"></i> Создать новую заявку</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>