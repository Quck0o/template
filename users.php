<?php
$page_title = "Управление пользователями - Колледж";
require_once 'header.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    
    if (empty($username) || empty($password) || empty($full_name) || empty($role)) {
        $error = "Заполните обязательные поля";
    } elseif (strlen($password) < 6) {
        $error = "Пароль должен содержать минимум 6 символов";
    } else {
        $result = registerUser($username, $password, $full_name, $role, $email);
        
        if ($result === true) {
            if (!empty($phone)) {
                $user_id = $pdo->lastInsertId();
                $stmt = $pdo->prepare("UPDATE users SET phone = ? WHERE id = ?");
                $stmt->execute([$phone, $user_id]);
            }
            $success = "Пользователь успешно добавлен";
        } else {
            $error = $result;
        }
    }
}

if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    
    if ($user_id == $_SESSION['user_id']) {
        $error = "Нельзя удалить собственный аккаунт";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $success = "Пользователь удален";
        } catch (Exception $e) {
            $error = "Ошибка: " . $e->getMessage();
        }
    }
}

if (isset($_GET['toggle_status'])) {
    $user_id = intval($_GET['toggle_status']);
    
    if ($user_id != $_SESSION['user_id']) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$user_id]);
            $success = "Статус пользователя изменен";
        } catch (Exception $e) {
            $error = "Ошибка: " . $e->getMessage();
        }
    }
}

$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';

$query = "SELECT * FROM users WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (username LIKE ? OR full_name LIKE ? OR email LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term]);
}

if (!empty($role_filter)) {
    $query .= " AND role = ?";
    $params[] = $role_filter;
}

$query .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_teachers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();
$total_students = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$total_staff = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'staff'")->fetchColumn();
?>

<div class="card">
    <h2>Управление пользователями</h2>
    <p>Всего пользователей: <?php echo $total_users; ?> (Преподавателей: <?php echo $total_teachers; ?>, Студентов: <?php echo $total_students; ?>, Сотрудников: <?php echo $total_staff; ?>)</p>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="grid-2">
    <div class="card">
        <h3>Добавить пользователя</h3>
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Логин:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Пароль:</label>
                <input type="password" id="password" name="password" required>
                <small style="color: #666;">Минимум 6 символов</small>
            </div>
            
            <div class="form-group">
                <label for="full_name">ФИО:</label>
                <input type="text" id="full_name" name="full_name" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email">
            </div>
            
            <div class="form-group">
                <label for="phone">Телефон:</label>
                <input type="text" id="phone" name="phone">
            </div>
            
            <div class="form-group">
                <label for="role">Роль:</label>
                <select id="role" name="role" required>
                    <option value="student">Студент</option>
                    <option value="teacher">Преподаватель</option>
                    <option value="staff">Сотрудник</option>
                    <option value="admin">Администратор</option>
                </select>
            </div>
            
            <button type="submit" name="add_user" class="btn">Добавить пользователя</button>
        </form>
    </div>
    
    <div class="card">
        <h3>Фильтр пользователей</h3>
        <form method="GET" action="" style="display: flex; gap: 10px; align-items: end;">
            <div class="form-group" style="flex: 2;">
                <label for="search">Поиск:</label>
                <input type="text" id="search" name="search" value="<?php echo escape($search); ?>" placeholder="Логин, ФИО или email">
            </div>
            
            <div class="form-group" style="flex: 1;">
                <label for="role">Роль:</label>
                <select id="role" name="role">
                    <option value="">Все</option>
                    <option value="admin" <?php echo $role_filter == 'admin' ? 'selected' : ''; ?>>Админ</option>
                    <option value="teacher" <?php echo $role_filter == 'teacher' ? 'selected' : ''; ?>>Преподаватель</option>
                    <option value="student" <?php echo $role_filter == 'student' ? 'selected' : ''; ?>>Студент</option>
                    <option value="staff" <?php echo $role_filter == 'staff' ? 'selected' : ''; ?>>Сотрудник</option>
                </select>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn">Фильтровать</button>
                <a href="users.php" class="btn btn-secondary" style="margin-left: 10px;">Сбросить</a>
            </div>
        </form>
        
        <div style="margin-top: 20px;">
            <h3>Список пользователей</h3>
            <?php if ($users): ?>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Логин</th>
                                <th>ФИО</th>
                                <th>Роль</th>
                                <th>Email</th>
                                <th>Дата регистрации</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><strong><?php echo escape($user['username']); ?></strong></td>
                                <td><?php echo escape($user['full_name']); ?></td>
                                <td>
                                    <span class="status <?php echo $user['role']; ?>">
                                        <?php
                                        $roles = [
                                            'admin' => 'Админ',
                                            'teacher' => 'Преподаватель',
                                            'student' => 'Студент',
                                            'staff' => 'Сотрудник'
                                        ];
                                        echo $roles[$user['role']];
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo escape($user['email']); ?></td>
                                <td><?php echo date('d.m.Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <a href="?delete=<?php echo $user['id']; ?>" 
                                               class="btn btn-danger" 
                                               style="padding: 5px 10px; font-size: 12px;"
                                               onclick="return confirm('Удалить пользователя <?php echo escape($user['full_name']); ?>?')">
                                                Удалить
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #666; font-size: 12px;">Текущий</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: #666; padding: 20px;">Пользователи не найдены</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card">
    <h3>Статистика по ролям</h3>
    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 200px;">
            <div style="background: #e74c3c; color: white; padding: 15px; border-radius: 4px; margin-bottom: 10px;">
                <h4 style="margin: 0; color: white;">Администраторы</h4>
                <div style="font-size: 24px; font-weight: bold; margin-top: 10px;"><?php echo $total_users > 0 ? round(($pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn() / $total_users) * 100, 1) : 0; ?>%</div>
                <div><?php echo $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn(); ?> чел.</div>
            </div>
        </div>
        
        <div style="flex: 1; min-width: 200px;">
            <div style="background: #3498db; color: white; padding: 15px; border-radius: 4px; margin-bottom: 10px;">
                <h4 style="margin: 0; color: white;">Преподаватели</h4>
                <div style="font-size: 24px; font-weight: bold; margin-top: 10px;"><?php echo $total_users > 0 ? round(($total_teachers / $total_users) * 100, 1) : 0; ?>%</div>
                <div><?php echo $total_teachers; ?> чел.</div>
            </div>
        </div>
        
        <div style="flex: 1; min-width: 200px;">
            <div style="background: #27ae60; color: white; padding: 15px; border-radius: 4px; margin-bottom: 10px;">
                <h4 style="margin: 0; color: white;">Студенты</h4>
                <div style="font-size: 24px; font-weight: bold; margin-top: 10px;"><?php echo $total_users > 0 ? round(($total_students / $total_users) * 100, 1) : 0; ?>%</div>
                <div><?php echo $total_students; ?> чел.</div>
            </div>
        </div>
        
        <div style="flex: 1; min-width: 200px;">
            <div style="background: #9b59b6; color: white; padding: 15px; border-radius: 4px; margin-bottom: 10px;">
                <h4 style="margin: 0; color: white;">Сотрудники</h4>
                <div style="font-size: 24px; font-weight: bold; margin-top: 10px;"><?php echo $total_users > 0 ? round(($total_staff / $total_users) * 100, 1) : 0; ?>%</div>
                <div><?php echo $total_staff; ?> чел.</div>
            </div>
        </div>
    </div>
</div>

</div>
</body>
</html>