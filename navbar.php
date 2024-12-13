<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>меню</title>
    <style>

        /* Кнопка для выезжающего меню */
        .menu-button {
            background-color: #333;
            color: white;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
            border: none;
            position: relative;
        }

        .menu-button:hover {
            background-color: #555;
        }

        /* Стили для бокового меню */
        .sidebar {
            display: none; /* По умолчанию меню скрыто */
            position: absolute;
            top: 40px;
            left: 0;
            width: 200px;
            background-color: #444;
            padding: 10px 0;
            border-radius: 0 0 5px 5px;
        }

        .sidebar a {
            display: block;
            padding: 10px;
            color: white;
            text-decoration: none;
        }

        .sidebar a:hover {
            background-color: #555;
        }

        /* Стили для отображения меню при наведении */
        .menu-container:hover .sidebar {
            display: block;
        }
    </style>
</head>
<body>

<!-- Контейнер для кнопки и выезжающего меню -->
<div class="menu-container">
    <button class="menu-button">Меню</button>
    <div class="sidebar">
        <a href="dashboard.php">Главная</a>
        <a href="equipment_library.php">Каталог оборудования</a>

        <?php if (isset($_SESSION['username'])): ?>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="users.php">Пользователи</a>
            <?php endif; ?>
            <?php if ($_SESSION['role'] === 'owner'): ?>
                <a href="owner_instrument.php">Инструмент арендодателя</a>
            <?php endif; ?>
            <?php if ($_SESSION['role'] === 'renter' || $_SESSION['role'] === 'owner'): ?>
                <a href="favorite.php">Избранное</a>
            <?php endif; ?>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="admin_instrument.php">Инструмент администратора</a>
            <?php endif; ?>
            <a href="logout.php">Выход</a>  
        <?php else: ?>
            <a href="login.php">Авторизация</a>
            <a href="registration.php">Регистрация</a>
        <?php endif; ?>
    </div>
</div>

</body>
</html>

