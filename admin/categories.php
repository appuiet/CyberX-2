<?php
// Проверка авторизации администратора
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Подключение к базе данных и функции
require_once '../config/db_connect.php';
require_once '../includes/functions.php';

// Обработка добавления новой категории
if (isset($_POST['add_category'])) {
    try {
        // Создаем slug из названия категории
        $slug = strtolower(preg_replace('/[^a-zA-Zа-яА-Я0-9]+/u', '-', $_POST['name']));
        $slug = trim($slug, '-');
        
        // Загрузка изображения
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../IMG/categories/';
            
            // Проверяем, существует ли директория, если нет - создаем
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $temp_name = $_FILES['image']['tmp_name'];
            $file_name = basename($_FILES['image']['name']);
            $image_path = 'IMG/categories/' . $file_name;
            
            // Перемещаем файл в директорию
            if (move_uploaded_file($temp_name, $upload_dir . $file_name)) {
                $success_message = "Изображение успешно загружено.";
            } else {
                $error_message = "Ошибка при загрузке изображения.";
            }
        }

        // Вставка данных в БД
        $stmt = $pdo->prepare("INSERT INTO categories (name, description, image, slug) 
                              VALUES (:name, :description, :image, :slug)");
        
        $stmt->bindParam(':name', $_POST['name']);
        $stmt->bindParam(':description', $_POST['description']);
        $stmt->bindParam(':image', $image_path);
        $stmt->bindParam(':slug', $slug);
        
        $stmt->execute();
        $success_message = "Категория успешно добавлена.";
    } catch (PDOException $e) {
        $error_message = "Ошибка при добавлении категории: " . $e->getMessage();
    }
}

// Обработка обновления категории
if (isset($_POST['update_category'])) {
    try {
        // Создаем slug из названия категории
        $slug = strtolower(preg_replace('/[^a-zA-Zа-яА-Я0-9]+/u', '-', $_POST['name']));
        $slug = trim($slug, '-');
        
        // Проверяем, было ли загружено новое изображение
        $image_sql = '';
        $params = [
            ':name' => $_POST['name'],
            ':description' => $_POST['description'],
            ':slug' => $slug,
            ':id' => $_POST['category_id']
        ];
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../IMG/categories/';
            
            // Проверяем, существует ли директория, если нет - создаем
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $temp_name = $_FILES['image']['tmp_name'];
            $file_name = basename($_FILES['image']['name']);
            $image_path = 'IMG/categories/' . $file_name;
            
            // Перемещаем файл в директорию
            if (move_uploaded_file($temp_name, $upload_dir . $file_name)) {
                $image_sql = ', image = :image';
                $params[':image'] = $image_path;
                $success_message = "Изображение успешно обновлено.";
            } else {
                $error_message = "Ошибка при загрузке изображения.";
            }
        }
        
        // Обновление данных в БД
        $stmt = $pdo->prepare("UPDATE categories SET 
                              name = :name, 
                              description = :description, 
                              slug = :slug" . 
                              $image_sql . 
                              " WHERE id = :id");
        
        $stmt->execute($params);
        $success_message = "Категория успешно обновлена.";
    } catch (PDOException $e) {
        $error_message = "Ошибка при обновлении категории: " . $e->getMessage();
    }
}

// Обработка удаления категории
if (isset($_POST['delete_category'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->bindParam(':id', $_POST['category_id']);
        $stmt->execute();
        $success_message = "Категория успешно удалена.";
    } catch (PDOException $e) {
        $error_message = "Ошибка при удалении категории: " . $e->getMessage();
    }
}

// Получение списка категорий
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Ошибка при получении категорий: " . $e->getMessage();
    $categories = [];
}

// Получение категории для редактирования
$edit_category = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->bindParam(':id', $_GET['edit']);
        $stmt->execute();
        $edit_category = $stmt->fetch();
    } catch (PDOException $e) {
        $error_message = "Ошибка при получении категории для редактирования: " . $e->getMessage();
    }
}

// Заголовок страницы
$page_title = "Управление категориями";
include 'admin_header.php';
?>


    <div class="row">
        <?php if (isset($success_message)): ?>
            <div class="col-md-12 mb-3">
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i><?= $success_message ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="col-md-12 mb-3">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i><?= $error_message ?>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-<?= $edit_category ? 'edit' : 'folder-plus' ?> me-2"></i>
                        <?= $edit_category ? 'Редактирование категории' : 'Добавление новой категории' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <?php if ($edit_category): ?>
                            <input type="hidden" name="category_id" value="<?= $edit_category['id'] ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold">
                                <i class="fas fa-folder me-1"></i> Название категории
                            </label>
                            <input type="text" class="form-control form-control-lg" id="name" name="name" 
                                value="<?= $edit_category ? htmlspecialchars($edit_category['name']) : '' ?>" required
                                placeholder="Введите название категории">
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label fw-bold">
                                <i class="fas fa-align-left me-1"></i> Описание
                            </label>
                            <textarea class="form-control" id="description" name="description" 
                                rows="5" placeholder="Детальное описание категории"><?= $edit_category ? htmlspecialchars($edit_category['description']) : '' ?></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <label for="image" class="form-label fw-bold">
                                <i class="fas fa-image me-1"></i> Изображение
                            </label>
                            <?php if ($edit_category && !empty($edit_category['image'])): ?>
                                <div class="mb-2 text-center">
                                    <img src="../<?= htmlspecialchars($edit_category['image']) ?>" alt="Текущее изображение" 
                                        class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                    <p class="small text-muted mt-1">
                                        <?= htmlspecialchars($edit_category['image']) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            <div class="input-group">
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <label class="input-group-text" for="image">Выбрать</label>
                            </div>
                            <small class="text-muted">Оставьте пустым, если не хотите менять изображение.</small>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <?php if ($edit_category): ?>
                                <button type="submit" name="update_category" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>Обновить категорию
                                </button>
                                <a href="categories.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Отмена
                                </a>
                            <?php else: ?>
                                <button type="submit" name="add_category" class="btn btn-success btn-lg">
                                    <i class="fas fa-plus-circle me-2"></i>Добавить категорию
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-folder-open me-2"></i>Список категорий
                    </h5>
                    <span class="badge bg-light text-dark"><?= count($categories) ?> категорий</span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($categories)): ?>
                        <div class="p-4 text-center">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>Категории не найдены
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center">#ID</th>
                                        <th>Изображение</th>
                                        <th>Название</th>
                                        <th>Slug</th>
                                        <th class="text-center">Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td class="text-center align-middle"><?= $category['id'] ?></td>
                                            <td class="align-middle">
                                                <?php if (!empty($category['image'])): ?>
                                                    <img src="../<?= htmlspecialchars($category['image']) ?>" 
                                                        alt="<?= htmlspecialchars($category['name']) ?>" 
                                                        class="img-thumbnail" style="max-width: 60px; max-height: 60px;">
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-image me-1"></i>Нет фото
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle">
                                                <strong><?= htmlspecialchars($category['name']) ?></strong>
                                            </td>
                                            <td class="align-middle">
                                                <code><?= htmlspecialchars($category['slug']) ?></code>
                                            </td>
                                            <td class="text-center align-middle">
                                                <div class="btn-group" role="group">
                                                    <a href="categories.php?edit=<?= $category['id'] ?>" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="post" class="d-inline" 
                                                        onsubmit="return confirm('Вы уверены, что хотите удалить эту категорию? Это также удалит все товары в этой категории!');">
                                                        <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                                                        <button type="submit" name="delete_category" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
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
    <?php include 'admin_footer.php'; ?> 