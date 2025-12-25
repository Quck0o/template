<?php
$page_title = "Оценки - Колледж";
require_once 'header.php';

if (!isLoggedIn() || (!isTeacher() && !isAdmin())) {
    redirect('dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_grade'])) {
    $student_id = intval($_POST['student_id']);
    $subject_id = intval($_POST['subject_id']);
    $grade = intval($_POST['grade']);
    $grade_type = $_POST['grade_type'];
    $comments = trim($_POST['comments']);
    
    if ($grade < 2 || $grade > 5) {
        $error = "Оценка должна быть от 2 до 5";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO grades (student_id, subject_id, teacher_id, grade, grade_type, comments, date_given) VALUES (?, ?, ?, ?, ?, ?, CURDATE())");
            $stmt->execute([$student_id, $subject_id, $_SESSION['user_id'], $grade, $grade_type, $comments]);
            $success = "Оценка успешно добавлена";
        } catch (Exception $e) {
            $error = "Ошибка: " . $e->getMessage();
        }
    }
}

$students = $pdo->query("SELECT * FROM users WHERE role = 'student' ORDER BY full_name")->fetchAll();
$subjects = $pdo->query("SELECT * FROM subjects ORDER BY name")->fetchAll();
$grades = $pdo->query("SELECT g.*, u.full_name as student_name, s.name as subject_name FROM grades g JOIN users u ON g.student_id = u.id JOIN subjects s ON g.subject_id = s.id ORDER BY g.date_given DESC")->fetchAll();
?>

<div class="card">
    <h2>Управление оценками</h2>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="grid-2">
    <div class="card">
        <h3>Добавить оценку</h3>
        <form method="POST" action="">
            <div class="form-group">
                <label for="student_id">Студент:</label>
                <select id="student_id" name="student_id" required>
                    <option value="">Выберите студента</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo $student['id']; ?>"><?php echo escape($student['full_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="subject_id">Предмет:</label>
                <select id="subject_id" name="subject_id" required>
                    <option value="">Выберите предмет</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo $subject['id']; ?>"><?php echo escape($subject['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="grade">Оценка:</label>
                <select id="grade" name="grade" required>
                    <option value="5">5 (Отлично)</option>
                    <option value="4">4 (Хорошо)</option>
                    <option value="3">3 (Удовлетворительно)</option>
                    <option value="2">2 (Неудовлетворительно)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="grade_type">Тип оценки:</label>
                <select id="grade_type" name="grade_type" required>
                    <option value="exam">Экзамен</option>
                    <option value="test">Зачет</option>
                    <option value="attestation">Аттестация</option>
                    <option value="coursework">Курсовая</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="comments">Комментарий:</label>
                <textarea id="comments" name="comments" rows="3"></textarea>
            </div>
            
            <button type="submit" name="add_grade" class="btn">Добавить оценку</button>
        </form>
    </div>
    
    <div class="card">
        <h3>Журнал оценок</h3>
        <?php if ($grades): ?>
            <table>
                <thead>
                    <tr>
                        <th>Студент</th>
                        <th>Предмет</th>
                        <th>Оценка</th>
                        <th>Тип</th>
                        <th>Дата</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grades as $grade): ?>
                    <tr>
                        <td><?php echo escape($grade['student_name']); ?></td>
                        <td><?php echo escape($grade['subject_name']); ?></td>
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
                        <td><?php echo date('d.m.Y', strtotime($grade['date_given'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center; color: #666; padding: 20px;">Оценок пока нет</p>
        <?php endif; ?>
    </div>
</div>

</div>
</body>
</html>