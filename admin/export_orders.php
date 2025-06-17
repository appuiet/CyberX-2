<?php
require_once '../config/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Проверяем права доступа
require_admin();

// Получаем параметры фильтрации
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$filter_date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Формируем SQL запрос с учетом фильтров
$sql = "SELECT o.*, u.username, u.email, u.phone, u.full_name 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE 1=1";
$params = [];

if (!empty($filter_status)) {
    $sql .= " AND o.status = :status";
    $params[':status'] = $filter_status;
}

if (!empty($filter_date_from)) {
    $sql .= " AND DATE(o.created_at) >= :date_from";
    $params[':date_from'] = $filter_date_from;
}

if (!empty($filter_date_to)) {
    $sql .= " AND DATE(o.created_at) <= :date_to";
    $params[':date_to'] = $filter_date_to;
}

$sql .= " ORDER BY o.created_at DESC";

// Подготовка и выполнение запроса
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$orders = $stmt->fetchAll();

// Функция для преобразования статуса заказа
function get_status_text($status) {
    switch ($status) {
        case 'pending':
            return 'В обработке';
        case 'processing':
            return 'Обрабатывается';
        case 'shipping':
            return 'Отправлен';
        case 'delivered':
            return 'Доставлен';
        case 'completed':
            return 'Выполнен';
        case 'cancelled':
            return 'Отменен';
        case 'refunded':
            return 'Возвращен';
        default:
            return $status;
    }
}

// Генерируем CSV файл
$filename = 'orders_export_' . date('Y-m-d_H-i-s') . '.csv';

// Заголовки для скачивания файла
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Открываем поток вывода
$output = fopen('php://output', 'w');

// Устанавливаем маркер BOM для корректного отображения кириллицы в Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Заголовки CSV файла
$header = [
    'ID заказа',
    'Дата создания',
    'Статус',
    'Имя пользователя',
    'Email',
    'ФИО',
    'Телефон',
    'Адрес доставки',
    'Метод оплаты',
    'Номер отслеживания',
    'Сумма заказа',
    'Комментарий'
];
fputcsv($output, $header, ';');

// Данные заказов
foreach ($orders as $order) {
    $row = [
        $order['id'],
        date('d.m.Y H:i', strtotime($order['created_at'])),
        get_status_text($order['status']),
        $order['username'] ?? 'Гость',
        $order['email'] ?? '',
        $order['full_name'] ?? '',
        $order['phone'] ?? '',
        $order['shipping_address'] ?? '',
        $order['payment_method'] ?? '',
        $order['tracking_number'] ?? '',
        $order['total_amount'],
        $order['comment'] ?? ''
    ];
    fputcsv($output, $row, ';');
}

// Закрываем поток
fclose($output);
exit; 