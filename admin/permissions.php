<?php
$page_title = 'Управление разрешениями';
require_once '../config/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Проверяем права доступа
require_admin();

// Обработка добавления нового разрешения
if (isset($_POST['add_permission'])) {
    $name = trim($_POST['name']);
    $slug = strtolower(trim($_POST['slug']));
    $module = trim($_POST['module']);
    $description = trim($_POST['description']);
    
    $errors = [];
    
    // Валидация
    if (empty($name)) {
        $errors[] = 'Название разрешения обязательно';
    }
    
    if (empty($slug)) {
        $errors[] = 'Идентификатор разрешения обязателен';
    }
    
    if (empty($module)) {
        $errors[] = 'Модуль обязателен';
    }
    
    // Проверка на существующее разрешение
    $stmt = $pdo->prepare("SELECT id FROM permissions WHERE slug = :slug");
    $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $errors[] = 'Разрешение с таким идентификатором уже существует';
    }
    
    if (empty($errors)) {
        // Добавляем разрешение
        $stmt = $pdo->prepare("INSERT INTO permissions (name, slug, module, description) 
                              VALUES (:name, :slug, :module, :description)");
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->bindParam(':module', $module, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Разрешение успешно добавлено';
            header("Location: permissions.php");
            exit;
        } else {
            $errors[] = 'Ошибка при добавлении разрешения';
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode('<br>', $errors);
    }
}

// Обработка редактирования разрешения
if (isset($_POST['edit_permission'])) {
    $permission_id = $_POST['permission_id'];
    $name = trim($_POST['name']);
    $module = trim($_POST['module']);
    $description = trim($_POST['description']);
    
    $errors = [];
    
    // Валидация
    if (empty($name)) {
        $errors[] = 'Название разрешения обязательно';
    }
    
    if (empty($module)) {
        $errors[] = 'Модуль обязателен';
    }
    
    if (empty($errors)) {
        // Обновляем разрешение
        $stmt = $pdo->prepare("UPDATE permissions SET 
                              name = :name, 
                              module = :module, 
                              description = :description 
                              WHERE id = :permission_id");
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':module', $module, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':permission_id', $permission_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Разрешение успешно обновлено';
            header("Location: permissions.php");
            exit;
        } else {
            $errors[] = 'Ошибка при обновлении разрешения';
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode('<br>', $errors);
    }
}

// Обработка удаления разрешения
if (isset($_POST['delete_permission'])) {
    $permission_id = $_POST['permission_id'];
    
    // Проверка использования разрешения в ролях
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM role_permissions WHERE permission_id = :permission_id");
    $stmt->bindParam(':permission_id', $permission_id, PDO::PARAM_INT);
    $stmt->execute();
    $roles_count = $stmt->fetch()['count'];
    
    if ($roles_count > 0) {
        $_SESSION['error_message'] = 'Невозможно удалить разрешение, которое используется в ролях';
    } else {
        $stmt = $pdo->prepare("DELETE FROM permissions WHERE id = :permission_id");
        $stmt->bindParam(':permission_id', $permission_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Разрешение успешно удалено';
        } else {
            $_SESSION['error_message'] = 'Ошибка при удалении разрешения';
        }
    }
    
    header("Location: permissions.php");
    exit;
}

// Получение данных для редактирования
$edit_permission = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $permission_id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM permissions WHERE id = :permission_id");
    $stmt->bindParam(':permission_id', $permission_id, PDO::PARAM_INT);
    $stmt->execute();
    $edit_permission = $stmt->fetch();
}

// Получение списка разрешений
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$module_filter = isset($_GET['module']) ? $_GET['module'] : '';

$sql = "SELECT p.*, COUNT(rp.role_id) as roles_count 
        FROM permissions p 
        LEFT JOIN role_permissions rp ON p.id = rp.permission_id 
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (p.name LIKE :search OR p.slug LIKE :search OR p.description LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if (!empty($module_filter)) {
    $sql .= " AND p.module = :module";
    $params[':module'] = $module_filter;
}

$sql .= " GROUP BY p.id ORDER BY p.module, p.name";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$permissions = $stmt->fetchAll();

// Получение уникальных модулей
$stmt = $pdo->query("SELECT DISTINCT module FROM permissions ORDER BY module");
$modules = $stmt->fetchAll(PDO::FETCH_COLUMN);
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
        .permissions-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        
        .permission-list, .permission-form {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        
        .search-form {
            display: flex;
            margin-bottom: 20px;
            gap: 10px;
        }
        
        .search-form input[type="text"] {
            flex: 1;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .module-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            color: #fff;
            background-color: #6c757d;
        }
        
        .roles-count {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 11px;
            background-color: #f8f9fa;
            color: #495057;
            margin-left: 5px;
        }
        
        @media screen and (max-width: 768px) {
            .permissions-container {
                grid-template-columns: 1fr;
            }
            
            .search-form {
                flex-direction: column;
            }
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
                    <li><a href="roles.php">Роли</a></li>
                    <li><a href="permissions.php" class="active">Разрешения</a></li>
                    <li><a href="feedback.php">Обратная связь</a></li>
                    <li><a href="../index.php">Вернуться на сайт</a></li>
                    <li><a href="../logout.php">Выйти</a></li>
                </ul>
            </nav>
        </div>
        
        <div class="admin-content">
            <div class="admin-header">
                <h1>Управление разрешениями</h1>
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
            
            <div class="permissions-container">
                <div class="permission-list">
                    <h3>Список разрешений</h3>
                    
                    <form class="search-form" method="get" action="permissions.php">
                        <input type="text" name="search" placeholder="Поиск по названию или описанию..." value="<?php echo htmlspecialchars($search); ?>">
                        <select name="module">
                            <option value="">Все модули</option>
                            <?php foreach ($modules as $module): ?>
                                <option value="<?php echo $module; ?>" <?php echo $module_filter === $module ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($module); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="button">Найти</button>
                        <a href="permissions.php" class="button button-delete">Сбросить</a>
                    </form>
                    
                    <?php if (empty($permissions)): ?>
                        <p>Разрешения не найдены</p>
                    <?php else: ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Название</th>
                                    <th>Идентификатор</th>
                                    <th>Модуль</th>
                                    <th>Используется</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($permissions as $permission): ?>
                                    <tr>
                                        <td><?php echo $permission['id']; ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($permission['name']); ?>
                                            <?php if (!empty($permission['description'])): ?>
                                                <div><small><?php echo htmlspecialchars($permission['description']); ?></small></div>
                                            <?php endif; ?>
                                        </td>
                                        <td><code><?php echo htmlspecialchars($permission['slug']); ?></code></td>
                                        <td><span class="module-badge"><?php echo htmlspecialchars($permission['module']); ?></span></td>
                                        <td>
                                            <span class="roles-count"><?php echo $permission['roles_count']; ?> ролей</span>
                                        </td>
                                        <td>
                                            <a href="permissions.php?edit=<?php echo $permission['id']; ?>" class="button button-edit">Редактировать</a>
                                            
                                            <?php if ($permission['roles_count'] == 0): ?>
                                                <form method="post" action="" style="display: inline;" onsubmit="return confirm('Вы уверены, что хотите удалить это разрешение?');">
                                                    <input type="hidden" name="permission_id" value="<?php echo $permission['id']; ?>">
                                                    <button type="submit" name="delete_permission" class="button button-delete">Удалить</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                
                <div class="permission-form">
                    <?php if ($edit_permission): ?>
                        <h3>Редактирование разрешения</h3>
                        <form method="post" action="">
                            <input type="hidden" name="permission_id" value="<?php echo $edit_permission['id']; ?>">
                            
                            <div class="form-group">
                                <label for="name">Название разрешения</label>
                                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($edit_permission['name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="slug">Идентификатор разрешения</label>
                                <input type="text" name="slug" id="slug" value="<?php echo htmlspecialchars($edit_permission['slug']); ?>" disabled>
                                <small>Идентификатор разрешения нельзя изменить</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="module">Модуль</label>
                                <input type="text" name="module" id="module" value="<?php echo htmlspecialchars($edit_permission['module']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Описание</label>
                                <textarea name="description" id="description" rows="3"><?php echo htmlspecialchars($edit_permission['description'] ?? ''); ?></textarea>
                            </div>
                            
                            <button type="submit" name="edit_permission" class="button">Сохранить изменения</button>
                            <a href="permissions.php" class="button button-delete">Отмена</a>
                        </form>
                    <?php else: ?>
                        <h3>Добавление нового разрешения</h3>
                        <form method="post" action="">
                            <div class="form-group">
                                <label for="name">Название разрешения</label>
                                <input type="text" name="name" id="name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="slug">Идентификатор разрешения</label>
                                <input type="text" name="slug" id="slug" required>
                                <small>Только буквы в нижнем регистре, цифры и символы подчеркивания</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="module">Модуль</label>
                                <input type="text" name="module" id="module" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Описание</label>
                                <textarea name="description" id="description" rows="3"></textarea>
                            </div>
                            
                            <button type="submit" name="add_permission" class="button">Добавить разрешение</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 