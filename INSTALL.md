# 🎮 CyberX - Инструкция по установке

## 📋 Требования

* PHP 7.4 или выше
* MySQL 5.7 или выше
* Git
* XAMPP (опционально)

## 🚀 Пошаговая установка

### 1️⃣ Клонирование проекта

```bash
# Если используете XAMPP, клонируйте в htdocs:
cd C:/xampp/htdocs    # для Windows
cd /Applications/XAMPP/htdocs    # для macOS
cd /opt/lampp/htdocs  # для Linux

# Клонируем репозиторий
git clone https://github.com/appuiet/CyberX.git

# Переходим в директорию проекта
cd CyberX
```

### 2️⃣ Настройка базы данных

1. Создайте новую базу данных:
   * Если используете XAMPP:
     - Откройте phpMyAdmin: http://localhost/phpmyadmin
     - Создайте новую базу данных `cyberx`
     - Выберите кодировку `utf8mb4_unicode_ci`
   
   * Если используете MySQL напрямую:
   ```sql
   CREATE DATABASE cyberx CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. Импортируйте структуру базы данных:
   * Через phpMyAdmin:
     - Откройте базу данных `cyberx`
     - Перейдите на вкладку "Импорт"
     - Выберите файл `database/cyberx.sql`
     - Нажмите "Выполнить"
   
   * Через командную строку:
   ```bash
   mysql -u root -p cyberx < database/cyberx.sql
   ```

3. Настройте подключение к БД:
   * Создайте файл `config/db_connect.php`:
   ```php
   <?php
   $host = 'localhost';
   $dbname = 'cyberx';
   $username = 'root';           // стандартный пользователь в XAMPP
   $password = '';              // пустой пароль в XAMPP
   
   try {
       $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
       $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   } catch(PDOException $e) {
       echo "Connection failed: " . $e->getMessage();
       exit;
   }
   ```

### 3️⃣ Запуск проекта

У вас есть два варианта запуска проекта:

#### Вариант 1: Через XAMPP (рекомендуется для разработки)

1. Установите XAMPP с официального сайта: https://www.apachefriends.org/
2. Клонируйте проект в папку htdocs:
   ```bash
   # Windows
   cd C:/xampp/htdocs
   # macOS
   cd /Applications/XAMPP/htdocs
   # Linux
   cd /opt/lampp/htdocs
   
   git clone https://github.com/your-username/CyberX.git
   ```
3. Запустите XAMPP Control Panel
4. Запустите модули Apache и MySQL
5. Откройте в браузере:
   * http://localhost/CyberX
   * или http://127.0.0.1/CyberX

#### Вариант 2: Через встроенный PHP сервер

1. Клонируйте проект в любую удобную папку:
   ```bash
   git clone https://github.com/your-username/CyberX.git
   cd CyberX
   ```

2. Запустите PHP сервер:
   ```bash
   # Windows (PowerShell или CMD)
   php -S localhost:8000

   # macOS/Linux (Terminal)
   php -S localhost:8000
   ```

3. Откройте в браузере:
   * http://localhost:8000

> ⚠️ Примечание: При использовании встроенного PHP сервера:
> * Сервер должен быть запущен из корневой папки проекта
> * Консоль должна оставаться открытой пока вы работаете с сайтом
> * Для остановки сервера нажмите Ctrl+C в консоли

### 4️⃣ Настройка прав доступа

```bash
# Установите права на директории
chmod -R 755 .
chmod -R 777 IMG/     # для загрузки изображений
```

### 5️⃣ Создание администратора

1. Зарегистрируйтесь через форму на сайте
2. Сделайте пользователя администратором:
   * Через phpMyAdmin:
     - Откройте таблицу `users`
     - Найдите вашего пользователя
     - Измените поле `role` на `admin`
   
   * Через SQL:
   ```sql
   UPDATE users SET role = 'admin' WHERE email = 'your_email@example.com';
   ```

## 🔍 Проверка установки

1. Откройте сайт в браузере:
   * Если PHP сервер: `http://localhost:8000`
   * Если XAMPP: `http://localhost/CyberX`

2. Проверьте:
   * Регистрацию и авторизацию
   * Просмотр каталога товаров
   * Работу корзины
   * Отправку форм обратной связи
   * Админ-панель (если вы администратор)

## ❗ Возможные проблемы

### Ошибка подключения к БД
* Проверьте данные в `config/db_connect.php`
* В XAMPP убедитесь, что MySQL запущен
* Для XAMPP стандартные данные:
  - пользователь: `root`
  - пароль: `` (пустой)

### Ошибки 404
* Убедитесь, что URL правильный
* Проверьте, что все файлы на месте
* В XAMPP проверьте, что Apache запущен

### Проблемы с загрузкой изображений
* Проверьте права на директорию IMG/
* В XAMPP проверьте настройки `php.ini`:
  - Откройте `php.ini` через XAMPP Control Panel
  - Найдите и измените:
    ```ini
    upload_max_filesize = 10M
    post_max_size = 10M
    ```

## 📱 Особенности проекта

* Адаптивный дизайн для всех устройств
* Интерактивные элементы на CSS и JavaScript
* Система авторизации и корзина покупок
* Формы обратной связи
* Админ-панель для управления товарами

## 🆘 Поддержка

При возникновении проблем:
1. Проверьте лог-файлы:
   * Для XAMPP: `C:/xampp/apache/logs/` или `/Applications/XAMPP/logs/`
   * Для PHP сервера: консоль, где запущен сервер
2. Создайте issue в репозитории
3. Свяжитесь с разработчиками через форму обратной связи
