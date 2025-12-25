<?php
require_once 'config.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            
            redirect('dashboard.php');
        } else {
            $error = "Неверные данные";
        }
    } else {
        $error = "Заполните все поля";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход - Колледж</title>
    <link rel="stylesheet" href="college_style.css">
</head>
<body>
    <div class="login-container">
        <h2>Вход в систему колледжа</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Логин:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Пароль:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn" style="width: 100%;">Войти</button>
        </form>
        
        <div style="margin-top: 20px; text-align: center; color: #666; font-size: 14px;">
            <strong>Тестовые пользователи:</strong><br>
            admin / password (Директор)<br>
            teacher1 / password (Преподаватель)<br>
            student1 / password (Студент)<br>
            staff1 / password (Сотрудник)
        </div>
    </div>
</body>
</html>