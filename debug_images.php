<?php
require_once 'config/db_connect.php';
require_once 'includes/functions.php';

// Получаем популярные товары
$featured_products = get_featured_products($pdo, 3);

// Выводим информацию о путях к изображениям
echo "<h1>Отладка путей к изображениям</h1>";

echo "<h2>Популярные товары:</h2>";
echo "<pre>";
foreach ($featured_products as $product) {
    echo "ID: " . $product['id'] . "\n";
    echo "Название: " . $product['name'] . "\n";
    echo "Путь к изображению в БД: " . $product['image'] . "\n";
    echo "Полный путь к изображению: IMG/" . $product['image'] . "\n";
    echo "Существование файла: " . (file_exists("IMG/" . $product['image']) ? "Да" : "Нет") . "\n";
    echo "-----------------------------------\n";
}
echo "</pre>";

// Проверяем существование файлов изображений из базы данных
echo "<h2>Проверка существования файлов:</h2>";
echo "<pre>";
$stmt = $pdo->query("SELECT id, name, image FROM products LIMIT 10");
$products = $stmt->fetchAll();
foreach ($products as $product) {
    echo "ID: " . $product['id'] . "\n";
    echo "Название: " . $product['name'] . "\n";
    echo "Путь к изображению: " . $product['image'] . "\n";
    echo "Существование файла: " . (file_exists("IMG/" . $product['image']) ? "Да" : "Нет") . "\n";
    echo "-----------------------------------\n";
}
echo "</pre>";

// Проверяем файлы в директории IMG
echo "<h2>Файлы в директории IMG:</h2>";
echo "<pre>";
$files = scandir("IMG");
foreach ($files as $file) {
    if ($file != "." && $file != "..") {
        echo $file . "\n";
    }
}
echo "</pre>";
?> 