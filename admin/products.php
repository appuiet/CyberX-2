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

// Обработка добавления нового товара
if (isset($_POST['add_product'])) {
    try {
        // Загрузка изображения
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../IMG/';
            $temp_name = $_FILES['image']['tmp_name'];
            $file_name = basename($_FILES['image']['name']);
            $image_path = 'IMG/' . $file_name;
            
            // Проверяем, существует ли директория, если нет - создаем
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Перемещаем файл в директорию
            if (move_uploaded_file($temp_name, $upload_dir . $file_name)) {
                $success_message = "Изображение успешно загружено.";
            } else {
                $error_message = "Ошибка при загрузке изображения.";
            }
        }

        // Вставка данных в БД
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image, category_id, stock) 
                              VALUES (:name, :description, :price, :image, :category_id, :stock)");
        
        $stmt->bindParam(':name', $_POST['name']);
        $stmt->bindParam(':description', $_POST['description']);
        $stmt->bindParam(':price', $_POST['price']);
        $stmt->bindParam(':image', $image_path);
        $stmt->bindParam(':category_id', $_POST['category_id']);
        $stmt->bindParam(':stock', $_POST['stock']);
        
        $stmt->execute();
        $success_message = "Товар успешно добавлен.";
    } catch (PDOException $e) {
        $error_message = "Ошибка при добавлении товара: " . $e->getMessage();
    }
}

// Обработка обновления товара
if (isset($_POST['update_product'])) {
    try {
        // Проверяем, было ли загружено новое изображение
        $image_sql = '';
        $params = [
            ':name' => $_POST['name'],
            ':description' => $_POST['description'],
            ':price' => $_POST['price'],
            ':category_id' => $_POST['category_id'],
            ':stock' => $_POST['stock'],
            ':id' => $_POST['product_id']
        ];
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../IMG/';
            $temp_name = $_FILES['image']['tmp_name'];
            $file_name = basename($_FILES['image']['name']);
            $image_path = 'IMG/' . $file_name;
            
            // Проверяем, существует ли директория, если нет - создаем
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
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
        $stmt = $pdo->prepare("UPDATE products SET 
                              name = :name, 
                              description = :description, 
                              price = :price, 
                              category_id = :category_id, 
                              stock = :stock" . 
                              $image_sql . 
                              " WHERE id = :id");
        
        $stmt->execute($params);
        $success_message = "Товар успешно обновлен.";
    } catch (PDOException $e) {
        $error_message = "Ошибка при обновлении товара: " . $e->getMessage();
    }
}

// Обработка удаления товара
if (isset($_POST['delete_product'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmt->bindParam(':id', $_POST['product_id']);
        $stmt->execute();
        $success_message = "Товар успешно удален.";
    } catch (PDOException $e) {
        $error_message = "Ошибка при удалении товара: " . $e->getMessage();
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

// Получение списка товаров с информацией о категориях
try {
    $stmt = $pdo->query("SELECT p.*, c.name as category_name 
                        FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        ORDER BY p.id DESC");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Ошибка при получении товаров: " . $e->getMessage();
    $products = [];
}

// Получение товара для редактирования
$edit_product = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->bindParam(':id', $_GET['edit']);
        $stmt->execute();
        $edit_product = $stmt->fetch();
    } catch (PDOException $e) {
        $error_message = "Ошибка при получении товара для редактирования: " . $e->getMessage();
    }
}

// Заголовок страницы
$page_title = "Управление товарами";
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
                        <i class="fas fa-<?= $edit_product ? 'edit' : 'plus-circle' ?> me-2"></i>
                        <?= $edit_product ? 'Редактирование товара' : 'Добавление нового товара' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <?php if ($edit_product): ?>
                            <input type="hidden" name="product_id" value="<?= $edit_product['id'] ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold">
                                <i class="fas fa-tag me-1"></i> Название товара
                            </label>
                            <input type="text" class="form-control form-control-lg" id="name" name="name" 
                                value="<?= $edit_product ? htmlspecialchars($edit_product['name']) : '' ?>" required
                                placeholder="Введите название товара">
                        </div>
                        
                        <div class="mb-3">
                            <label for="category_id" class="form-label fw-bold">
                                <i class="fas fa-folder me-1"></i> Категория
                            </label>
                            <select class="form-select form-select-lg" id="category_id" name="category_id" required>
                                <option value="">Выберите категорию</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" <?= ($edit_product && $edit_product['category_id'] == $category['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label fw-bold">
                                <i class="fas fa-align-left me-1"></i> Описание
                            </label>
                            <textarea class="form-control" id="description" name="description" 
                                rows="5" placeholder="Детальное описание товара"><?= $edit_product ? htmlspecialchars($edit_product['description']) : '' ?></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="price" class="form-label fw-bold">
                                    <i class="fas fa-tag me-1"></i> Цена (BYN)
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">BYN</span>
                                    <input type="number" class="form-control" id="price" name="price" 
                                        step="0.01" value="<?= $edit_product ? $edit_product['price'] : '' ?>" required
                                        placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="stock" class="form-label fw-bold">
                                    <i class="fas fa-boxes me-1"></i> Количество
                                </label>
                                <input type="number" class="form-control" id="stock" name="stock" 
                                    value="<?= $edit_product ? $edit_product['stock'] : '0' ?>" required
                                    placeholder="0">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="image" class="form-label fw-bold">
                                <i class="fas fa-image me-1"></i> Изображение
                            </label>
                            <?php if ($edit_product && !empty($edit_product['image'])): ?>
                                <div class="mb-2 text-center">
                                    <img src="../<?= htmlspecialchars($edit_product['image']) ?>" alt="Текущее изображение" 
                                        class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                    <p class="small text-muted mt-1">
                                        <?= htmlspecialchars($edit_product['image']) ?>
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
                            <?php if ($edit_product): ?>
                                <button type="submit" name="update_product" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>Обновить товар
                                </button>
                                <a href="products.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Отмена
                                </a>
                            <?php else: ?>
                                <button type="submit" name="add_product" class="btn btn-success btn-lg">
                                    <i class="fas fa-plus-circle me-2"></i>Добавить товар
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
                        <i class="fas fa-list me-2"></i>Список товаров
                    </h5>
                    <span class="badge bg-light text-dark"><?= count($products) ?> товаров</span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($products)): ?>
                        <div class="p-4 text-center">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>Товары не найдены
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
                                        <th>Категория</th>
                                        <th>Цена</th>
                                        <th class="text-center">В наличии</th>
                                        <th class="text-center">Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td class="text-center align-middle"><?= $product['id'] ?></td>
                                            <td class="align-middle">
                                                <?php if (!empty($product['image'])): ?>
                                                    <img src="../<?= htmlspecialchars($product['image']) ?>" 
                                                        alt="<?= htmlspecialchars($product['name']) ?>" 
                                                        class="img-thumbnail" style="max-width: 60px; max-height: 60px;">
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-image me-1"></i>Нет фото
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle">
                                                <strong><?= htmlspecialchars($product['name']) ?></strong>
                                            </td>
                                            <td class="align-middle">
                                                <span class="badge bg-info text-dark">
                                                    <i class="fas fa-folder me-1"></i><?= htmlspecialchars($product['category_name'] ?? 'Без категории') ?>
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                <span class="fw-bold text-success"><?= format_price($product['price']) ?> BYN</span>
                                            </td>
                                            <td class="text-center align-middle">
                                                <?php if (($product['stock'] ?? 0) > 10): ?>
                                                    <span class="badge bg-success"><?= $product['stock'] ?></span>
                                                <?php elseif (($product['stock'] ?? 0) > 0): ?>
                                                    <span class="badge bg-warning text-dark"><?= $product['stock'] ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Нет в наличии</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center align-middle">
                                                <div class="btn-group" role="group">
                                                    <a href="products.php?edit=<?= $product['id'] ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="post" class="d-inline" onsubmit="return confirm('Вы уверены, что хотите удалить этот товар?');">
                                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                                        <button type="submit" name="delete_product" class="btn btn-sm btn-danger">
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
