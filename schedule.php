<?php
$page_title = "Расписание - Колледж";
require_once 'header.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$day_names = ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];

if (isStudent()) {
    $student_id = $_SESSION['user_id'];
    $schedule = $pdo->query("SELECT s.*, sub.name as subject_name, u.full_name as teacher_name, g.name as group_name 
                           FROM schedules s 
                           JOIN subjects sub ON s.subject_id = sub.id 
                           JOIN users u ON s.teacher_id = u.id 
                           JOIN groups g ON s.group_id = g.id 
                           WHERE s.group_id IN (SELECT id FROM groups WHERE name LIKE '%21%') 
                           ORDER BY s.day_of_week, s.lesson_number")->fetchAll();
} elseif (isTeacher()) {
    $teacher_id = $_SESSION['user_id'];
    $schedule = $pdo->prepare("SELECT s.*, sub.name as subject_name, g.name as group_name, g.course 
                             FROM schedules s 
                             JOIN subjects sub ON s.subject_id = sub.id 
                             JOIN groups g ON s.group_id = g.id 
                             WHERE s.teacher_id = ? 
                             ORDER BY s.day_of_week, s.lesson_number");
    $schedule->execute([$teacher_id]);
    $schedule = $schedule->fetchAll();
} else {
    $schedule = $pdo->query("SELECT s.*, sub.name as subject_name, u.full_name as teacher_name, g.name as group_name 
                           FROM schedules s 
                           JOIN subjects sub ON s.subject_id = sub.id 
                           JOIN users u ON s.teacher_id = u.id 
                           JOIN groups g ON s.group_id = g.id 
                           ORDER BY s.day_of_week, s.lesson_number")->fetchAll();
}

$grouped_schedule = [];
foreach ($schedule as $lesson) {
    $day = $lesson['day_of_week'];
    if (!isset($grouped_schedule[$day])) {
        $grouped_schedule[$day] = [];
    }
    $grouped_schedule[$day][] = $lesson;
}
?>

<div class="card">
    <h2>Расписание занятий</h2>
</div>

<?php if (empty($schedule)): ?>
    <div class="card">
        <p style="text-align: center; color: #666; padding: 20px;">Расписание пока не составлено</p>
    </div>
<?php else: ?>
    <?php foreach ($grouped_schedule as $day => $lessons): ?>
        <div class="card">
            <h3><?php echo $day_names[$day-1]; ?></h3>
            <table>
                <thead>
                    <tr>
                        <th>Пара</th>
                        <th>Предмет</th>
                        <th>Группа</th>
                        <th>Преподаватель</th>
                        <th>Аудитория</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lessons as $lesson): ?>
                    <tr>
                        <td><?php echo $lesson['lesson_number']; ?></td>
                        <td><strong><?php echo escape($lesson['subject_name']); ?></strong></td>
                        <td><?php echo escape($lesson['group_name']); ?></td>
                        <td><?php echo isset($lesson['teacher_name']) ? escape($lesson['teacher_name']) : '-'; ?></td>
                        <td><?php echo escape($lesson['room']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</div>
</body>
</html>