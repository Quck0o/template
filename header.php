<?php
if (!isset($pdo)) {
    require_once 'config.php';
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Колледж'; ?></title>
    <link rel="stylesheet" href="college_style.css">
</head>
<body>
    <div class="header">
        <h1>Система управления колледжем</h1>
        <div class="user-info">
            <div>
                <div class="user-name"><?php echo escape($_SESSION['full_name']); ?></div>
                <div class="user-role"><?php echo escape($_SESSION['role']); ?></div>
            </div>
            <a href="?logout" class="logout-btn">Выйти</a>
        </div>
    </div>
    
    <div class="sidebar">
        <ul>
            <li><a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>"> Главная</a></li>
            
            <?php if (isAdmin() || isTeacher()): ?>
            <li><a href="schedule.php" class="<?php echo $current_page == 'schedule.php' ? 'active' : ''; ?>"> Расписание</a></li>
            <li><a href="grades.php" class="<?php echo $current_page == 'grades.php' ? 'active' : ''; ?>"> Оценки</a></li>
            <?php endif; ?>
            
            <?php if (isAdmin()): ?>
            <li><a href="users.php" class="<?php echo $current_page == 'users.php' ? 'active' : ''; ?>"> Пользователи</a></li>
            <li><a href="departments.php" class="<?php echo $current_page == 'departments.php' ? 'active' : ''; ?>"> Отделения</a></li>
            <?php endif; ?>
            
            <?php if (isTeacher()): ?>
            <li><a href="assignments.php" class="<?php echo $current_page == 'assignments.php' ? 'active' : ''; ?>"> Задания</a></li>
            <?php endif; ?>
            
            <?php if (isStudent()): ?>
            <li><a href="my_grades.php" class="<?php echo $current_page == 'my_grades.php' ? 'active' : ''; ?>"> Мои оценки</a></li>
            <li><a href="my_assignments.php" class="<?php echo $current_page == 'my_assignments.php' ? 'active' : ''; ?>"> Мои задания</a></li>
            <?php endif; ?>
            
            <li><a href="news.php" class="<?php echo $current_page == 'news.php' ? 'active' : ''; ?>">Новости</a></li>
        </ul>
    </div>
    
    <div class="main-content">