<?php
$page_title = "Главная - Колледж";
require_once 'header.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

if (isset($_GET['logout'])) {
    session_destroy();
    redirect('index.php');
}

$stats = [];

if (isAdmin()) {
    $stats['users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $stats['teachers'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();
    $stats['students'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
    $stats['departments'] = $pdo->query("SELECT COUNT(*) FROM departments")->fetchColumn();
}

if (isTeacher()) {
    $teacher_id = $_SESSION['user_id'];
    $stats['subjects'] = $pdo->prepare("SELECT COUNT(DISTINCT subject_id) FROM schedules WHERE teacher_id = ?")->execute([$teacher_id])->fetchColumn();
    $stats['groups'] = $pdo->prepare("SELECT COUNT(DISTINCT group_id) FROM schedules WHERE teacher_id = ?")->execute([$teacher_id])->fetchColumn();
}

if (isStudent()) {
    $student_id = $_SESSION['user_id'];
    $stats['average'] = $pdo->prepare("SELECT AVG(grade) FROM grades WHERE student_id = ?")->execute([$student_id])->fetchColumn();
    $stats['subjects'] = $pdo->prepare("SELECT COUNT(DISTINCT subject_id) FROM grades WHERE student_id = ?")->execute([$student_id])->fetchColumn();
}

$news = $pdo->query("SELECT n.*, u.full_name FROM news n LEFT JOIN users u ON n.author_id = u.id WHERE is_published = TRUE ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<div class="card">
    <h2>Добро пожаловать, <?php echo escape($_SESSION['full_name']); ?>!</h2>
    <p style="color: #666;">Роль: <?php echo escape($_SESSION['role']); ?> | <?php echo date('d.m.Y'); ?></p>
</div>

<?php if (!empty($stats)): ?>
<div class="stats">
    <?php foreach ($stats as $key => $value): ?>
    <div class="stat-card">
        <h3><?php echo ucfirst($key); ?></h3>
        <div class="number"><?php echo is_numeric($value) ? number_format($value, 1) : $value; ?></div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="grid-2">
    <div class="card">
        <h3>Последние новости</h3>
        <?php if ($news): ?>
            <?php foreach ($news as $item): ?>
            <div style="padding: 15px 0; border-bottom: 1px solid var(--border-color);">
                <h4 style="margin-bottom: 5px;"><?php echo escape($item['title']); ?></h4>
                <p style="color: #666; font-size: 14px; margin-bottom: 10px;">
                    <?php echo date('d.m.Y', strtotime($item['created_at'])); ?> | <?php echo escape($item['full_name']); ?>
                </p>
                <p><?php echo substr(escape($item['content']), 0, 150); ?>...</p>
                <a href="news.php#news-<?php echo $item['id']; ?>" style="color: var(--secondary-color); font-size: 14px;">Подробнее</a>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; color: #666; padding: 20px;">Новостей пока нет</p>
        <?php endif; ?>
    </div>
    
    <div class="card">
        <h3>Быстрые действия</h3>
        <div class="quick-actions">
            <?php if (isAdmin()): ?>
                <a href="users.php" class="btn"> Управление пользователями</a>
                <a href="departments.php" class="btn" style="margin-top: 10px;"> Управление отделениями</a>
            <?php endif; ?>
            
            <?php if (isTeacher()): ?>
                <a href="grades.php" class="btn"> Выставить оценки</a>
                <a href="assignments.php" class="btn" style="margin-top: 10px;"> Создать задание</a>
            <?php endif; ?>
            
            <?php if (isStudent()): ?>
                <a href="my_grades.php" class="btn"> Посмотреть оценки</a>
                <a href="my_assignments.php" class="btn" style="margin-top: 10px;"> Мои задания</a>
            <?php endif; ?>
            
            <a href="schedule.php" class="btn" style="margin-top: 10px; background: var(--accent-color);"> Расписание</a>
        </div>
    </div>
</div>

</div>
</body>
</html>