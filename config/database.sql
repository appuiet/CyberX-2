-- Создание базы данных
CREATE DATABASE IF NOT EXISTS cyberx_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE cyberx_db;

-- Таблица пользователей
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Таблица категорий товаров
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    slug VARCHAR(100) NOT NULL UNIQUE
);

-- Таблица товаров
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    stock INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Таблица корзины
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Таблица элементов корзины
CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cart_id) REFERENCES cart(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Таблица заказов
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    total_amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Таблица элементов заказа
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Таблица обратной связи
CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Вставка тестовых данных для категорий
INSERT INTO categories (name, description, image, slug) VALUES
('Видеокарты', 'Графические ускорители для вашего компьютера', 'videocards.jpg', 'videocards'),
('Процессоры', 'Мощные процессоры для любых задач', 'processors.jpg', 'processors'),
('Гарнитура', 'Качественные наушники для геймеров', 'headsets.jpg', 'headsets');

-- Вставка тестовых данных для товаров
INSERT INTO products (category_id, name, description, price, image, stock) VALUES
(1, 'Gigabyte GeForce RTX 5070 Gaming OC 12G', 'Мощная видеокарта для игр и работы', 2389.90, 'one.png', 10),
(1, 'Palit GeForce RTX 5090 GameRock', 'Топовая видеокарта для требовательных задач', 12237.93, 'two.png', 5),
(1, 'Gigabyte GeForce RTX 3060 Gaming OC 12GB GDDR6', 'Отличное соотношение цена/качество', 1154.60, 'p.png', 15),
(1, 'Gigabyte GeForce RTX 5070 Aero OC 12G', 'Видеокарта с эффективной системой охлаждения', 2869.82, 'fff.png', 8),
(1, 'MSI GeForce RTX 5060 Ti 16G Gaming Trio OC', 'Видеокарта среднего уровня с хорошей производительностью', 2329.11, '600.png', 12),
(1, 'GeForce RTX 2060 GAMING OC PRO WHITE 6G', 'Видеокарта с уникальным белым дизайном', 1242.89, 'sas.png', 7),
(1, 'AORUS GeForce RTX 5080 XTREME WATERFORCE 16G', 'Видеокарта с жидкостным охлаждением', 2670.99, 'hjhj.png', 4),
(1, 'AORUS GeForce RTX 5090 MASTER 32G', 'Флагманская видеокарта для профессионалов', 6297.12, 'ujuj.png', 3),
(2, 'AMD Ryzen 9 7950X', 'Топовый процессор для многозадачности', 1899.90, 'ryzen9.png', 10),
(2, 'Intel Core i9-14900K', 'Мощный процессор для игр и работы', 2099.90, 'intel_i9.png', 8),
(2, 'AMD Ryzen 7 7800X3D', 'Игровой процессор с технологией 3D V-Cache', 1499.90, 'ryzen7.png', 15),
(3, 'HyperX Cloud Alpha', 'Профессиональная игровая гарнитура', 299.90, 'hyperx.png', 20),
(3, 'Logitech G Pro X', 'Киберспортивная гарнитура с микрофоном Blue VO!CE', 349.90, 'logitech.png', 15),
(3, 'SteelSeries Arctis Pro', 'Премиальная гарнитура с высоким качеством звука', 399.90, 'steelseries.png', 10);

-- Вставка тестовых пользователей с обновленными паролями
INSERT INTO users (username, password, email, full_name, phone, role) VALUES
('admin', '$2y$10$rBl1wVFx5OYUO.rLgDlSb.7R/ghCFGCT.zYwY0Dk4VwQvyQd8iuIa', 'admin@cyberx.com', 'Администратор', '+375291234567', 'admin'),
('user1', '$2y$10$hJlIX9BL1Xq4/uhM9sE/4.9wehqnYFLBnwlgbX1FS3CJxLBJvKNHa', 'user1@example.com', 'Иван Иванов', '+375297654321', 'user'); 