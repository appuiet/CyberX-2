<?php
// Заголовок страницы
$page_title = "Управление пользователями";
include 'admin_header.php';

// Получение списка пользователей
try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Ошибка при получении пользователей: " . $e->getMessage();
    $users = [];
}

// Обработка изменения роли пользователя
if (isset($_POST['change_role'])) {
    try {
        $stmt = $pdo->prepare("UPDATE users SET role = :role WHERE id = :user_id");
        $stmt->bindParam(':role', $_POST['role']);
        $stmt->bindParam(':user_id', $_POST['user_id']);
        $stmt->execute();
        $success_message = "Роль пользователя успешно изменена.";
    } catch (PDOException $e) {
        $error_message = "Ошибка при изменении роли пользователя: " . $e->getMessage();
    }
}

// Обработка удаления пользователя
if (isset($_POST['delete_user'])) {
    try {
        // Проверяем, не является ли пользователь текущим администратором
        if ($_POST['user_id'] == $_SESSION['user_id']) {
            $error_message = "Вы не можете удалить свою учетную запись.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :user_id");
            $stmt->bindParam(':user_id', $_POST['user_id']);
            $stmt->execute();
            $success_message = "Пользователь успешно удален.";
        }
    } catch (PDOException $e) {
        $error_message = "Ошибка при удалении пользователя: " . $e->getMessage();
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Список пользователей</h5>
            </div>
            <div class="card-body">
                <?php if (empty($users)): ?>
                    <p>Пользователи не найдены.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Имя пользователя</th>
                                    <th>Email</th>
                                    <th>Полное имя</th>
                                    <th>Телефон</th>
                                    <th>Роль</th>
                                    <th>Дата регистрации</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= $user['id'] ?></td>
                                        <td><?= htmlspecialchars($user['username']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td><?= htmlspecialchars($user['full_name']) ?></td>
                                        <td><?= htmlspecialchars($user['phone'] ?? 'Не указан') ?></td>
                                        <td>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <select name="role" class="form-select form-select-sm" onchange="this.form.submit()" <?= $user['id'] == $_SESSION['user_id'] ? 'disabled' : '' ?>>
                                                    <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>Пользователь</option>
                                                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Администратор</option>
                                                </select>
                                                <input type="hidden" name="change_role" value="1">
                                            </form>
                                        </td>
                                        <td><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></td>
                                        <td>
                                            <form method="post" class="d-inline" onsubmit="return confirm('Вы уверены, что хотите удалить этого пользователя?');">
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <button type="submit" name="delete_user" class="btn btn-sm btn-danger" <?= $user['id'] == $_SESSION['user_id'] ? 'disabled' : '' ?>>Удалить</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>
