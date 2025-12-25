<?php
$page_title = "Мои оценки - Колледж";
require_once 'header.php';

if (!isLoggedIn() || !isStudent()) {
    redirect('dashboard.php');
}

$student_id = $_SESSION['user_id'];

$grades = $pdo->prepare("SELECT g.*, s.name as subject_name, u.full_name as teacher_name 
                        FROM grades g 
                        JOIN subjects s ON g.subject_id = s.id 
                        JOIN users u ON g.teacher_id = u.id 
                        WHERE g.student_id = ? 
                        ORDER BY g.date_given DESC");
$grades->execute([$student_id]);
$grades = $grades->fetchAll();

$average = $pdo->prepare("SELECT AVG(grade) FROM grades WHERE student_id = ?");
$average->execute([$student_id]);
$average = $average->fetchColumn();
?>

<div class="card">
    <h2>Мои оценки</h2>
</div>

<div class="stats">
    <div class="stat-card">
        <h3>Средний балл</h3>
        <div class="number"><?php echo number_format($average, 1); ?></div>
    </div>
    
    <div class="stat-card">
        <h3>Всего оценок</h3>
        <div class="number"><?php echo count($grades); ?></div>
    </div>
    
    <div class="stat-card">
        <h3>Предметов</h3>
        <?php
        $subjects_count = $pdo->prepare("SELECT COUNT(DISTINCT subject_id) FROM grades WHERE student_id = ?");
        $subjects_count->execute([$student_id]);
        ?>
        <div class="number"><?php echo $subjects_count->fetchColumn(); ?></div>
    </div>
</div>

<div class="card">
    <h3>История оценок</h3>
    <?php if ($grades): ?>
        <table>
            <thead>
                <tr>
                    <th>Предмет</th>
                    <th>Оценка</th>
                    <th>Тип</th>
                    <th>Преподаватель</th>
                    <th>Дата</th>
                    <th>Комментарий</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($grades as $grade): ?>
                <tr>
                    <td><strong><?php echo escape($grade['subject_name']); ?></strong></td>
                    <td>
                        <span class="badge <?php echo $grade['grade'] >= 4 ? 'badge-success' : 'badge-warning'; ?>">
                            <?php echo $grade['grade']; ?>
                        </span>
                    </td>
                    <td>
                        <?php
                        $types = [
                            'exam' => 'Экзамен',
                            'test' => 'Зачет',
                            'attestation' => 'Аттестация',
                            'coursework' => 'Курсовая'
                        ];
                        echo $types[$grade['grade_type']];
                        ?>
                    </td>
                    <td><?php echo escape($grade['teacher_name']); ?></td>
                    <td><?php echo date('d.m.Y', strtotime($grade['date_given'])); ?></td>
                    <td><?php echo escape($grade['comments']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align: center; color: #666; padding: 20px;">У вас пока нет оценок</p>
    <?php endif; ?>
</div>

</div>
</body>
</html>