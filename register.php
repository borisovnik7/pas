<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['admin']) && $_SESSION['admin']) {
        header('Location: admin.php');
    } else {
        header('Location: create.php');
    }
    exit;
}

$error = false;
$error_message = '';
$success = false;
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    
    $form_data = compact('login', 'fullname', 'phone', 'email');
    
    $errors = [];
    
    if (empty($login)) {
        $errors[] = 'Логин обязателен для заполнения';
    } elseif (!preg_match('/^[a-zA-Z0-9]{6,}$/', $login)) {
        $errors[] = 'Логин должен содержать только латиницу и цифры, минимум 6 символов';
    }
    
    if (empty($password)) {
        $errors[] = 'Пароль обязателен для заполнения';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Пароль должен содержать минимум 8 символов';
    }
    
    if (empty($fullname)) {
        $errors[] = 'ФИО обязательно для заполнения';
    } elseif (strlen($fullname) < 5) {
        $errors[] = 'Введите полное ФИО';
    }
    
    if (empty($phone)) {
        $errors[] = 'Телефон обязателен для заполнения';
    } elseif (!preg_match('/^\+7\(\d{3}\)\d{3}-\d{2}-\d{2}$/', $phone)) {
        $errors[] = 'Телефон должен быть в формате +7(XXX)XXX-XX-XX';
    }
    
    if (empty($email)) {
        $errors[] = 'Email обязателен для заполнения';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный email';
    }
    
    if (empty($errors)) {
        include('db.php');
        
        $stmt = $con->prepare("SELECT id FROM users WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = true;
            $error_message = 'Пользователь с таким логином уже существует';
        } else {
            $stmt = $con->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = true;
                $error_message = 'Пользователь с таким email уже существует';
            } else {
                $stmt = $con->prepare("INSERT INTO users (login, password, fullname, phone, email) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $login, $password, $fullname, $phone, $email);
                
                if ($stmt->execute()) {
                    $success = true;
                    header('refresh:2;url=login.php');
                } else {
                    $error = true;
                    $error_message = 'Ошибка при регистрации: ' . $con->error;
                }
                $stmt->close();
            }
        }
        $stmt->close();
    } else {
        $error = true;
        $error_message = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация — Пассажирам.РФ</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --success: #10b981;
            --danger: #ef4444;
            --dark: #1e293b;
            --gray: #64748b;
            --white: #ffffff;
            --border: #e2e8f0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem;
        }

        .register-container {
            max-width: 550px;
            margin: 0 auto;
            background: var(--white);
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo i {
            font-size: 3rem;
            color: var(--primary);
        }

        .logo h1 {
            font-size: 1.75rem;
            margin-top: 0.5rem;
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-header h2 {
            font-size: 1.5rem;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .error-message, .success-message {
            padding: 0.75rem 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .error-message {
            background: #fee2e2;
            color: var(--danger);
        }

        .success-message {
            background: #d1fae5;
            color: var(--success);
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
            font-size: 0.875rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .hint {
            font-size: 0.75rem;
            color: var(--gray);
            margin-top: 0.25rem;
            display: block;
        }

        .btn-register {
            width: 100%;
            padding: 0.875rem;
            background: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 0.5rem;
        }

        .btn-register:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .form-footer {
            margin-top: 1.5rem;
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
        }

        .form-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <i class="fas fa-bus"></i>
            <h1>Пассажирам.РФ</h1>
        </div>

        <div class="form-header">
            <h2>Создание аккаунта</h2>
            <p>Заполните форму для регистрации</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                Регистрация успешно завершена! Перенаправление...
            </div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST" action="">
            <div class="form-group">
                <label><i class="fas fa-user"></i> ФИО</label>
                <input type="text" name="fullname" placeholder="Иванов Иван Иванович" required value="<?php echo htmlspecialchars($form_data['fullname'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label><i class="fas fa-phone"></i> Телефон</label>
                <input type="tel" name="phone" placeholder="+7(XXX)XXX-XX-XX" pattern="\+7\(\d{3}\)\d{3}-\d{2}-\d{2}" required value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>">
                <span class="hint">Формат: +7(XXX)XXX-XX-XX</span>
            </div>

            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email</label>
                <input type="email" name="email" placeholder="example@mail.com" required value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label><i class="fas fa-key"></i> Логин</label>
                <input type="text" name="login" placeholder="ivan123" pattern="[a-zA-Z0-9]{6,}" required value="<?php echo htmlspecialchars($form_data['login'] ?? ''); ?>">
                <span class="hint">Только латиница и цифры, минимум 6 символов</span>
            </div>

            <div class="form-group">
                <label><i class="fas fa-lock"></i> Пароль</label>
                <input type="password" name="password" placeholder="Минимум 8 символов" minlength="8" required>
                <span class="hint">Минимум 8 символов</span>
            </div>

            <button type="submit" class="btn-register">Зарегистрироваться</button>
        </form>
        <?php endif; ?>

        <div class="form-footer">
            <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
            <p style="margin-top: 0.5rem;"><a href="index.php">← Вернуться на главную</a></p>
        </div>
    </div>
</body>
</html>