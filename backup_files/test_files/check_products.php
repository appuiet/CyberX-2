<?php
// Включаем отображение всех ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Подключаем необходимые файлы
require_once 'config/db_connect.php';
require_once 'includes/functions.php';

echo "<h1>Проверка и обновление данных о продуктах</h1>";

// Проверяем наличие продуктов в базе данных
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
    $result = $stmt->fetch();
    $count = $result['count'];
    
    echo "<p>Количество продуктов в базе данных: {$count}</p>";
    
    if ($count > 0) {
        echo "<h2>Список продуктов:</h2>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Название</th><th>Цена</th><th>Изображение</th><th>Категория</th></tr>";
        
        $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id");
        $products = $stmt->fetchAll();
        
        foreach ($products as $product) {
            echo "<tr>";
            echo "<td>{$product['id']}</td>";
            echo "<td>{$product['name']}</td>";
            echo "<td>{$product['price']} BYN</td>";
            echo "<td>{$product['image']}</td>";
            echo "<td>{$product['category_name']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>Продукты не найдены. Создаю тестовые продукты...</p>";
        
        // Проверяем наличие категорий
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
        $result = $stmt->fetch();
        $category_count = $result['count'];
        
        if ($category_count == 0) {
            // Создаем тестовые категории
            $categories = [
                ['name' => 'Видеокарты', 'description' => 'Графические ускорители для игр и рабочих станций'],
                ['name' => 'Процессоры', 'description' => 'CPU для настольных компьютеров и серверов'],
                ['name' => 'Периферия', 'description' => 'Клавиатуры, мыши, гарнитуры и другие устройства ввода-вывода']
            ];
            
            foreach ($categories as $category) {
                $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (:name, :description)");
                $stmt->bindParam(':name', $category['name']);
                $stmt->bindParam(':description', $category['description']);
                $stmt->execute();
                
                echo "<p>Создана категория: {$category['name']}</p>";
            }
        }
        
        // Получаем ID категорий
        $stmt = $pdo->query("SELECT id, name FROM categories LIMIT 3");
        $categories = $stmt->fetchAll();
        
        // Создаем тестовые продукты
        $products = [
            [
                'name' => 'Gigabyte GeForce RTX 5070 Gaming OC 12G',
                'description' => 'Мощная видеокарта для игр и профессиональных задач с поддержкой трассировки лучей.',
                'price' => 2389.90,
                'image' => 'IMG/one.png',
                'category_id' => $categories[0]['id']
            ],
            [
                'name' => 'Palit GeForce RTX 5090 GameRock',
                'description' => 'Высокопроизводительный процессор для геймеров и контент-мейкеров.',
                'price' => 12237.93,
                'image' => 'IMG/two.png',
                'category_id' => $categories[0]['id']
            ],
            [
                'name' => 'HyperX Cloud Alpha',
                'description' => 'Профессиональная игровая гарнитура с отличным звучанием и комфортом.',
                'price' => 399.00,
                'image' => 'IMG/p.png',
                'category_id' => $categories[2]['id']
            ]
        ];
        
        foreach ($products as $product) {
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image, category_id) VALUES (:name, :description, :price, :image, :category_id)");
            $stmt->bindParam(':name', $product['name']);
            $stmt->bindParam(':description', $product['description']);
            $stmt->bindParam(':price', $product['price']);
            $stmt->bindParam(':image', $product['image']);
            $stmt->bindParam(':category_id', $product['category_id']);
            $stmt->execute();
            
            echo "<p>Создан продукт: {$product['name']}</p>";
        }
        
        echo "<p>Тестовые продукты созданы успешно!</p>";
    }
    
    // Проверяем корректность путей к изображениям
    echo "<h2>Проверка путей к изображениям:</h2>";
    
    $stmt = $pdo->query("SELECT id, name, image FROM products");
    $products_to_update = [];
    
    while ($product = $stmt->fetch()) {
        $image_path = $product['image'];
        $correct_path = null;
        
        // Проверяем, начинается ли путь с IMG/
        if (strpos($image_path, 'IMG/') !== 0) {
            $filename = basename($image_path);
            
            if (file_exists("IMG/{$filename}")) {
                $correct_path = "IMG/{$filename}";
            } else {
                // Проверяем доступные изображения
                if ($product['id'] == 1) {
                    $correct_path = "IMG/one.png";
                } else if ($product['id'] == 2) {
                    $correct_path = "IMG/two.png";
                } else if ($product['id'] == 3) {
                    $correct_path = "IMG/p.png";
                }
            }
            
            if ($correct_path) {
                $products_to_update[] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'old_image' => $image_path,
                    'new_image' => $correct_path
                ];
            }
        } else {
            // Проверяем, существует ли файл
            $file_path = $image_path;
            
            if (!file_exists($file_path)) {
                // Находим замену
                if ($product['id'] == 1) {
                    $correct_path = "IMG/one.png";
                } else if ($product['id'] == 2) {
                    $correct_path = "IMG/two.png";
                } else if ($product['id'] == 3) {
                    $correct_path = "IMG/p.png";
                }
                
                if ($correct_path) {
                    $products_to_update[] = [
                        'id' => $product['id'],
                        'name' => $product['name'],
                        'old_image' => $image_path,
                        'new_image' => $correct_path
                    ];
                }
            }
        }
    }
    
    // Обновляем пути к изображениям
    if (!empty($products_to_update)) {
        echo "<p>Необходимо обновить пути к изображениям для следующих продуктов:</p>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Название</th><th>Старый путь</th><th>Новый путь</th></tr>";
        
        foreach ($products_to_update as $product) {
            echo "<tr>";
            echo "<td>{$product['id']}</td>";
            echo "<td>{$product['name']}</td>";
            echo "<td>{$product['old_image']}</td>";
            echo "<td>{$product['new_image']}</td>";
            echo "</tr>";
            
            // Обновляем путь в базе данных
            $stmt = $pdo->prepare("UPDATE products SET image = :image WHERE id = :id");
            $stmt->bindParam(':image', $product['new_image']);
            $stmt->bindParam(':id', $product['id']);
            $stmt->execute();
        }
        
        echo "</table>";
        echo "<p>Пути к изображениям обновлены успешно!</p>";
    } else {
        echo "<p>Все пути к изображениям корректны.</p>";
    }
} catch (PDOException $e) {
    echo "<p>Ошибка при работе с базой данных: " . $e->getMessage() . "</p>";
}

echo "<p><a href='shop.php'>Вернуться в магазин</a></p>";
?> 