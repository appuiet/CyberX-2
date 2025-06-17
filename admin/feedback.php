<?php
// Заголовок страницы
$page_title = "Обратная связь";
include 'admin_header.php';

// Обработка удаления сообщения
if (isset($_POST['delete_message'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM feedback WHERE id = :message_id");
        $stmt->bindParam(':message_id', $_POST['message_id']);
        $stmt->execute();
        $success_message = "Сообщение успешно удалено.";
    } catch (PDOException $e) {
        $error_message = "Ошибка при удалении сообщения: " . $e->getMessage();
    }
}

// Получение списка сообщений обратной связи
try {
    $stmt = $pdo->query("SELECT f.*, u.username FROM feedback f 
                        LEFT JOIN users u ON f.user_id = u.id 
                        ORDER BY f.created_at DESC");
    $messages = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Ошибка при получении сообщений: " . $e->getMessage();
    $messages = [];
}

// Получение деталей сообщения
$message_details = null;
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    try {
        $stmt = $pdo->prepare("SELECT f.*, u.username, u.email FROM feedback f 
                              LEFT JOIN users u ON f.user_id = u.id 
                              WHERE f.id = :message_id");
        $stmt->bindParam(':message_id', $_GET['view']);
        $stmt->execute();
        $message_details = $stmt->fetch();
    } catch (PDOException $e) {
        $error_message = "Ошибка при получении деталей сообщения: " . $e->getMessage();
    }
}
?>

<?php if (isset($_GET['view']) && $message_details): ?>
    <!-- Детали сообщения -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-envelope-open-text me-2"></i>Просмотр сообщения #<?= $message_details['id'] ?>
                    </h5>
                    <a href="feedback.php" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Вернуться к списку
                    </a>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Информация о сообщении</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span class="fw-bold"><i class="fas fa-heading me-1"></i>Тема:</span>
                                            <span><?= empty($message_details['subject']) ? '<span class="text-muted">Без темы</span>' : htmlspecialchars($message_details['subject']) ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span class="fw-bold"><i class="fas fa-calendar-alt me-1"></i>Дата:</span>
                                            <span class="badge bg-secondary"><?= date('d.m.Y H:i', strtotime($message_details['created_at'])) ?></span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-user me-2"></i>Информация о пользователе</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span class="fw-bold"><i class="fas fa-user me-1"></i>Имя:</span>
                                            <span><?= htmlspecialchars($message_details['name']) ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span class="fw-bold"><i class="fas fa-envelope me-1"></i>Email:</span>
                                            <span><?= htmlspecialchars($message_details['email']) ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span class="fw-bold"><i class="fas fa-user-circle me-1"></i>Пользователь:</span>
                                            <span>
                                                <?php if ($message_details['username']): ?>
                                                    <span class="badge bg-success"><?= htmlspecialchars($message_details['username']) ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Гость</span>
                                                <?php endif; ?>
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-comment-alt me-2"></i>Текст сообщения</h6>
                        </div>
                        <div class="card-body">
                            <div class="p-3 bg-light rounded border">
                                <?= nl2br(htmlspecialchars($message_details['message'])) ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="feedback.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Назад
                        </a>
                        <form method="post" onsubmit="return confirm('Вы уверены, что хотите удалить это сообщение?');">
                            <input type="hidden" name="message_id" value="<?= $message_details['id'] ?>">
                            <button type="submit" name="delete_message" class="btn btn-danger">
                                <i class="fas fa-trash me-2"></i>Удалить сообщение
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Список сообщений -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-comments me-2"></i>Список сообщений обратной связи
                    </h5>
                    <span class="badge bg-light text-dark"><?= count($messages) ?> сообщений</span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($messages)): ?>
                        <div class="p-4 text-center">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>Сообщения не найдены
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center">#ID</th>
                                        <th><i class="fas fa-user me-1"></i>Имя</th>
                                        <th><i class="fas fa-envelope me-1"></i>Email</th>
                                        <th><i class="fas fa-heading me-1"></i>Тема</th>
                                        <th><i class="fas fa-calendar me-1"></i>Дата</th>
                                        <th class="text-center">Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($messages as $message): ?>
                                        <tr>
                                            <td class="text-center align-middle"><?= $message['id'] ?></td>
                                            <td class="align-middle"><strong><?= htmlspecialchars($message['name']) ?></strong></td>
                                            <td class="align-middle"><?= htmlspecialchars($message['email']) ?></td>
                                            <td class="align-middle">
                                                <?php if (empty($message['subject'])): ?>
                                                    <span class="text-muted">Без темы</span>
                                                <?php else: ?>
                                                    <?= htmlspecialchars($message['subject']) ?>
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle">
                                                <span class="badge bg-secondary">
                                                    <?= date('d.m.Y H:i', strtotime($message['created_at'])) ?>
                                                </span>
                                            </td>
                                            <td class="text-center align-middle">
                                                <div class="btn-group" role="group">
                                                    <a href="feedback.php?view=<?= $message['id'] ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <form method="post" class="d-inline" 
                                                          onsubmit="return confirm('Вы уверены, что хотите удалить это сообщение?');">
                                                        <input type="hidden" name="message_id" value="<?= $message['id'] ?>">
                                                        <button type="submit" name="delete_message" class="btn btn-sm btn-danger">
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
    </div>
<?php endif; ?>

<?php include 'admin_footer.php'; ?>
