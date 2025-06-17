<?php
$page_title = 'Управление ролями';
require_once '../config/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Проверяем права доступа
require_admin();

// Обработка добавления новой роли
if (isset($_POST['add_role'])) {
    $role_name = trim($_POST['role_name']);
    $role_slug = strtolower(trim($_POST['role_slug']));
    $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];
    
    $errors = [];
    
    // Валидация
    if (empty($role_name)) {
        $errors[] = 'Название роли обязательно';
    }
    
    if (empty($role_slug)) {
        $errors[] = 'Идентификатор роли обязателен';
    }
    
    // Проверка на существующую роль
    $stmt = $pdo->prepare("SELECT id FROM roles WHERE slug = :slug");
    $stmt->bindParam(':slug', $role_slug, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $errors[] = 'Роль с таким идентификатором уже существует';
    }
    
    if (empty($errors)) {
        // Добавляем роль
        $stmt = $pdo->prepare("INSERT INTO roles (name, slug, description) VALUES (:name, :slug, :description)");
        $stmt->bindParam(':name', $role_name, PDO::PARAM_STR);
        $stmt->bindParam(':slug', $role_slug, PDO::PARAM_STR);
        $stmt->bindParam(':description', $_POST['description'], PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            $role_id = $pdo->lastInsertId();
            
            // Добавляем разрешения для роли
            if (!empty($permissions)) {
                $values = [];
                $params = [];
                
                foreach ($permissions as $i => $perm_id) {
                    $values[] = "(:role_id{$i}, :perm_id{$i})";
                    $params[":role_id{$i}"] = $role_id;
                    $params[":perm_id{$i}"] = $perm_id;
                }
                
                $sql = "INSERT INTO role_permissions (role_id, permission_id) VALUES " . implode(', ', $values);
                $stmt = $pdo->prepare($sql);
                
                foreach ($params as $key => $value) {
                    $stmt->bindValue($key, $value);
                }
                
                $stmt->execute();
            }
            
            $_SESSION['success_message'] = 'Роль успешно добавлена';
            header("Location: roles.php");
            exit;
        } else {
            $errors[] = 'Ошибка при добавлении роли';
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode('<br>', $errors);
    }
}

// Обработка редактирования роли
if (isset($_POST['edit_role'])) {
    $role_id = $_POST['role_id'];
    $role_name = trim($_POST['role_name']);
    $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];
    
    $errors = [];
    
    // Валидация
    if (empty($role_name)) {
        $errors[] = 'Название роли обязательно';
    }
    
    if (empty($errors)) {
        // Обновляем роль
        $stmt = $pdo->prepare("UPDATE roles SET name = :name, description = :description WHERE id = :role_id");
        $stmt->bindParam(':name', $role_name, PDO::PARAM_STR);
        $stmt->bindParam(':description', $_POST['description'], PDO::PARAM_STR);
        $stmt->bindParam(':role_id', $role_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            // Удаляем текущие разрешения роли
            $stmt = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = :role_id");
            $stmt->bindParam(':role_id', $role_id, PDO::PARAM_INT);
            $stmt->execute();
            
            // Добавляем новые разрешения для роли
            if (!empty($permissions)) {
                $values = [];
                $params = [];
                
                foreach ($permissions as $i => $perm_id) {
                    $values[] = "(:role_id{$i}, :perm_id{$i})";
                    $params[":role_id{$i}"] = $role_id;
                    $params[":perm_id{$i}"] = $perm_id;
                }
                
                $sql = "INSERT INTO role_permissions (role_id, permission_id) VALUES " . implode(', ', $values);
                $stmt = $pdo->prepare($sql);
                
                foreach ($params as $key => $value) {
                    $stmt->bindValue($key, $value);
                }
                
                $stmt->execute();
            }
            
            $_SESSION['success_message'] = 'Роль успешно обновлена';
            header("Location: roles.php");
            exit;
        } else {
            $errors[] = 'Ошибка при обновлении роли';
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode('<br>', $errors);
    }
}

// Обработка удаления роли
if (isset($_POST['delete_role'])) {
    $role_id = $_POST['role_id'];
    
    // Проверяем, есть ли пользователи с этой ролью
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role_id = :role_id");
    $stmt->bindParam(':role_id', $role_id, PDO::PARAM_INT);
    $stmt->execute();
    $users_count = $stmt->fetch()['count'];
    
    if ($users_count > 0) {
        $_SESSION['error_message'] = 'Невозможно удалить роль, которая назначена пользователям. Сначала измените роль этих пользователей.';
    } else {
        try {
            // Начинаем транзакцию
            $pdo->beginTransaction();
            
            // Удаляем разрешения роли
            $stmt = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = :role_id");
            $stmt->bindParam(':role_id', $role_id, PDO::PARAM_INT);
            $stmt->execute();
            
            // Удаляем саму роль
            $stmt = $pdo->prepare("DELETE FROM roles WHERE id = :role_id");
            $stmt->bindParam(':role_id', $role_id, PDO::PARAM_INT);
            $stmt->execute();
            
            // Завершаем транзакцию
            $pdo->commit();
            
            $_SESSION['success_message'] = 'Роль успешно удалена';
        } catch (PDOException $e) {
            // Откатываем транзакцию в случае ошибки
            $pdo->rollBack();
            $_SESSION['error_message'] = 'Ошибка при удалении роли: ' . $e->getMessage();
        }
    }
    
    header("Location: roles.php");
    exit;
}

// Получение данных для редактирования
$edit_role = null;
$role_permissions = [];

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $role_id = $_GET['edit'];
    
    // Получаем данные роли
    $stmt = $pdo->prepare("SELECT * FROM roles WHERE id = :role_id");
    $stmt->bindParam(':role_id', $role_id, PDO::PARAM_INT);
    $stmt->execute();
    $edit_role = $stmt->fetch();
    
    if ($edit_role) {
        // Получаем разрешения роли
        $stmt = $pdo->prepare("SELECT permission_id FROM role_permissions WHERE role_id = :role_id");
        $stmt->bindParam(':role_id', $role_id, PDO::PARAM_INT);
        $stmt->execute();
        $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $role_permissions = $permissions;
    }
}

// Получение списка ролей
$stmt = $pdo->query("SELECT r.*, COUNT(u.id) as users_count 
                    FROM roles r 
                    LEFT JOIN users u ON r.slug = u.role OR r.id = u.role_id
                    GROUP BY r.id 
                    ORDER BY r.id");
$roles = $stmt->fetchAll();

// Получение списка всех разрешений
$stmt = $pdo->query("SELECT * FROM permissions ORDER BY module, name");
$all_permissions = $stmt->fetchAll();

// Группировка разрешений по модулям
$permissions_by_module = [];
foreach ($all_permissions as $permission) {
    $module = $permission['module'];
    if (!isset($permissions_by_module[$module])) {
        $permissions_by_module[$module] = [];
    }
    $permissions_by_module[$module][] = $permission;
}

// Получение статистики
$stmt = $pdo->query("SELECT COUNT(*) as count FROM roles");
$roles_count = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM permissions");
$permissions_count = $stmt->fetch()['count'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $page_title; ?> - CYBERX</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        .roles-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .role-list, .role-form {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        
        .permission-section {
            margin-bottom: 20px;
            border: 1px solid #eee;
            border-radius: 5px;
            padding: 10px;
        }
        
        .permission-section h4 {
            margin-top: 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }
        
        .permission-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 10px;
        }
        
        .permission-item {
            margin-bottom: 10px;
        }
        
        .role-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            color: #fff;
            background-color: #6c757d;
        }
        
        .users-count {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 11px;
            background-color: #f8f9fa;
            color: #495057;
            margin-left: 5px;
        }
        
        .stats-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .stat-box {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 15px;
            flex: 1;
            margin-right: 10px;
            text-align: center;
        }
        
        .stat-box:last-child {
            margin-right: 0;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 14px;
            color: #666;
        }
        
        @media screen and (max-width: 768px) {
            .roles-container {
                grid-template-columns: 1fr;
            }
        }
        
        .select-all-permissions {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-sidebar">
            <div class="admin-logo">
                <img src="../IMG/logo.jpg" alt="Логотип" class="logo-image">
                <h2>Панель администратора</h2>
            </div>
            <nav class="admin-menu">
                <ul>
                    <li><a href="index.php">Главная</a></li>
                    <li><a href="products.php">Товары</a></li>
                    <li><a href="categories.php">Категории</a></li>
                    <li><a href="orders.php">Заказы</a></li>
                    <li><a href="users.php">Пользователи</a></li>
                    <li><a href="roles.php" class="active">Роли и разрешения</a></li>
                    <li><a href="feedback.php">Обратная связь</a></li>
                    <li><a href="../index.php">Вернуться на сайт</a></li>
                    <li><a href="../logout.php">Выйти</a></li>
                </ul>
            </nav>
        </div>
        
        <div class="admin-content">
            <div class="admin-header">
                <h1>Управление ролями и разрешениями</h1>
                <div class="admin-user">
                    <span>Вы вошли как: <?php echo $_SESSION['username']; ?></span>
                </div>
            </div>
            
            <?php
            // Вывод сообщений об ошибках или успехе
            if (isset($_SESSION['error_message'])) {
                echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
                unset($_SESSION['error_message']);
            }
            
            if (isset($_SESSION['success_message'])) {
                echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
                unset($_SESSION['success_message']);
            }
            ?>
            
            <!-- Статистика -->
            <div class="stats-container">
                <div class="stat-box">
                    <div class="stat-number"><?php echo $roles_count; ?></div>
                    <div class="stat-label">Всего ролей</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo $permissions_count; ?></div>
                    <div class="stat-label">Всего разрешений</div>
                </div>
            </div>
            
            <div class="roles-container">
                <div class="role-list">
                    <h3>Список ролей</h3>
                    
                    <?php if (empty($roles)): ?>
                        <p>Роли не найдены</p>
                    <?php else: ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Название</th>
                                    <th>Идентификатор</th>
                                    <th>Пользователи</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($roles as $role): ?>
                                    <tr>
                                        <td><?php echo $role['id']; ?></td>
                                        <td><?php echo htmlspecialchars($role['name']); ?></td>
                                        <td><span class="role-badge"><?php echo htmlspecialchars($role['slug']); ?></span></td>
                                        <td>
                                            <span class="users-count"><?php echo $role['users_count']; ?></span>
                                        </td>
                                        <td>
                                            <a href="roles.php?edit=<?php echo $role['id']; ?>" class="button button-edit">Редактировать</a>
                                            
                                            <?php if ($role['users_count'] == 0): ?>
                                                <form method="post" action="" style="display: inline;" onsubmit="return confirm('Вы уверены, что хотите удалить эту роль?');">
                                                    <input type="hidden" name="role_id" value="<?php echo $role['id']; ?>">
                                                    <button type="submit" name="delete_role" class="button button-delete">Удалить</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                
                <div class="role-form">
                    <?php if ($edit_role): ?>
                        <h3>Редактирование роли</h3>
                        <form method="post" action="">
                            <input type="hidden" name="role_id" value="<?php echo $edit_role['id']; ?>">
                            
                            <div class="form-group">
                                <label for="role_name">Название роли</label>
                                <input type="text" name="role_name" id="role_name" value="<?php echo htmlspecialchars($edit_role['name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="role_slug">Идентификатор роли</label>
                                <input type="text" name="role_slug" id="role_slug" value="<?php echo htmlspecialchars($edit_role['slug']); ?>" disabled>
                                <small>Идентификатор роли нельзя изменить</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Описание</label>
                                <textarea name="description" id="description" rows="3"><?php echo htmlspecialchars($edit_role['description'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Разрешения</label>
                                <div class="select-all-permissions">
                                    <label>
                                        <input type="checkbox" id="select-all"> Выбрать все разрешения
                                    </label>
                                </div>
                                
                                <?php foreach ($permissions_by_module as $module => $module_permissions): ?>
                                    <div class="permission-section">
                                        <h4><?php echo htmlspecialchars($module); ?></h4>
                                        <div class="permission-list">
                                            <?php foreach ($module_permissions as $permission): ?>
                                                <div class="permission-item">
                                                    <label>
                                                        <input type="checkbox" name="permissions[]" value="<?php echo $permission['id']; ?>" 
                                                            <?php echo in_array($permission['id'], $role_permissions) ? 'checked' : ''; ?>>
                                                        <?php echo htmlspecialchars($permission['name']); ?>
                                                    </label>
                                                    <?php if (!empty($permission['description'])): ?>
                                                        <div><small><?php echo htmlspecialchars($permission['description']); ?></small></div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <button type="submit" name="edit_role" class="button">Сохранить изменения</button>
                            <a href="roles.php" class="button button-delete">Отмена</a>
                        </form>
                    <?php else: ?>
                        <h3>Добавление новой роли</h3>
                        <form method="post" action="">
                            <div class="form-group">
                                <label for="role_name">Название роли</label>
                                <input type="text" name="role_name" id="role_name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="role_slug">Идентификатор роли</label>
                                <input type="text" name="role_slug" id="role_slug" required>
                                <small>Только буквы в нижнем регистре, цифры и символы подчеркивания</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Описание</label>
                                <textarea name="description" id="description" rows="3"></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Разрешения</label>
                                <div class="select-all-permissions">
                                    <label>
                                        <input type="checkbox" id="select-all"> Выбрать все разрешения
                                    </label>
                                </div>
                                
                                <?php foreach ($permissions_by_module as $module => $module_permissions): ?>
                                    <div class="permission-section">
                                        <h4><?php echo htmlspecialchars($module); ?></h4>
                                        <div class="permission-list">
                                            <?php foreach ($module_permissions as $permission): ?>
                                                <div class="permission-item">
                                                    <label>
                                                        <input type="checkbox" name="permissions[]" value="<?php echo $permission['id']; ?>">
                                                        <?php echo htmlspecialchars($permission['name']); ?>
                                                    </label>
                                                    <?php if (!empty($permission['description'])): ?>
                                                        <div><small><?php echo htmlspecialchars($permission['description']); ?></small></div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <button type="submit" name="add_role" class="button">Добавить роль</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // JavaScript для выбора всех разрешений
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('select-all');
            const permissionCheckboxes = document.querySelectorAll('input[name="permissions[]"]');
            
            if (selectAllCheckbox && permissionCheckboxes.length > 0) {
                // Обработка выбора всех разрешений
                selectAllCheckbox.addEventListener('change', function() {
                    const isChecked = this.checked;
                    
                    permissionCheckboxes.forEach(function(checkbox) {
                        checkbox.checked = isChecked;
                    });
                });
                
                // Обновление состояния "выбрать все" в зависимости от выбранных разрешений
                function updateSelectAllCheckbox() {
                    let allChecked = true;
                    
                    permissionCheckboxes.forEach(function(checkbox) {
                        if (!checkbox.checked) {
                            allChecked = false;
                        }
                    });
                    
                    selectAllCheckbox.checked = allChecked;
                }
                
                // Обновляем состояние при загрузке страницы
                updateSelectAllCheckbox();
                
                // Добавляем обработчики для каждого разрешения
                permissionCheckboxes.forEach(function(checkbox) {
                    checkbox.addEventListener('change', updateSelectAllCheckbox);
                });
            }
        });
    </script>
</body>
</html> 