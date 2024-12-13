<?php
require('db.php');
session_start();

// Проверка соединения с базой данных
$db_error = "";
if (!$con) {
    $db_error = "Ошибка подключения к базе данных.";
}

// Поиск
$search_query = '';
if (isset($_GET['search']) && $con) {
    $search_query = mysqli_real_escape_string($con, $_GET['search']);
}


$query = "SELECT id, equipment_name, owner, image FROM equipment ";
if (!empty($search_query)) {
    $query .= "WHERE (equipment_name LIKE '%$search_query%' OR owner LIKE '%$search_query%') ";
}
$query .= "ORDER BY dateadd DESC";

$equipments_result = mysqli_query($con, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог оборудования</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Arial', sans-serif;
        background-color: #f4f4f9;
        color: #333;
        line-height: 1.6;
        padding: 5px;
    }

    .content {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    h1 {
        text-align: center;
        margin-bottom: 20px;
        color: #333;
    }

    /* Стили формы поиска */
    .search-form {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
    }

    .search-form input[type="text"] {
        width: 60%;
        max-width: 400px;
        padding: 10px 15px;
        font-size: 16px;
        border: 1px solid #ddd;
        border-radius: 5px 0 0 5px;
    }

    .search-form button {
        padding: 10px 20px;
        font-size: 16px;
        border: none;
        background-color: #4CAF50;
        color: #fff;
        border-radius: 0 5px 5px 0;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .search-form button:hover {
        background-color: #45a049;
    }

    .error {
        text-align: center;
        background-color: #f8d7da;
        color: #721c24;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .equipment-grid {
        display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 30px;
    }

    .equipment-card {
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
}

.equipment-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
}

.equipment-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-bottom: 5px solid #4CAF50; /* Нижняя рамка под картинкой */
    transition: transform 0.3s ease;
}
   
    .equipment-card p {
        padding: 10px 15px;
        font-size: 16px;
        color: #555;
    }

    .equipment-card p a {
        color: #333;
        text-decoration: none;
        font-weight: bold;
    }

    .equipment-card p a:hover {
        color: #4CAF50;
    }

    .equipment-card strong {
        font-weight: bold;
        color: #555;
    }
    .equipment-card:hover .equipment-image {
    transform: scale(1.25); /* Увеличение при наведении */
}

.equipment-image-circle {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 50%; /* Делаем картинку круглой */
    border: 5px solid #4CAF50; /* Рамка вокруг круга */
}

.equipment-card-content {
    padding: 15px;
}

.equipment-card-content p {
    font-size: 16px;
    margin-bottom: 10px;
    color: #555;
}
.equipment-card-content a {
    color: #333;
    text-decoration: none;
    font-weight: bold;
}

.equipment-card-content a:hover {
    color: #4CAF50;
}

/* Стиль для наложения текста поверх изображения */
.image-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    background: rgba(0, 0, 0, 0.6); /* Чёрный полупрозрачный фон */
    color: #fff;
    text-align: center;
    padding: 10px 0;
    font-size: 16px;
    transition: opacity 0.3s ease;
    opacity: 0; /* Скрыт по умолчанию */
}

.equipment-card:hover .image-overlay {
    opacity: 1; /* Показываем при наведении */
}
    /* Адаптивность */
    @media (max-width: 768px) {
        .search-form input[type="text"] {
            width: 100%;
            border-radius: 5px;
        }

        .search-form button {
            border-radius: 5px;
            margin-top: 10px;
            width: 100%;
        }

        .equipment-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

    <link rel="stylesheet" type="text/css" href="style.css">

</head>
<body>

<?php include 'navbar.php'; ?>

<div class="content">
    <?php if ($db_error): ?>
        <p class="error"><?php echo $db_error; ?></p>
    <?php else: ?>
        <!-- Поисковая форма -->
        <form method="GET" action="equipment_library.php" class="search-form">
            <input type="text" name="search" placeholder="Введите название оборудования или владельца" value="<?php echo htmlspecialchars($search_query); ?>" />
            <button type="submit">Поиск</button>
        </form>

        <h1>Каталог оборудования</h1>

        <!-- Сетка оборудования -->
        <div class="equipment-grid">
            <?php if ($equipments_result && mysqli_num_rows($equipments_result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($equipments_result)): ?>
                    <div class="equipment-card">
                        <a href="equipment.php?id=<?php echo $row['id']; ?>">
                            <img src="uploads/<?php echo $row['image']; ?>" alt="<?php echo $row['equipment_name']; ?>" class="equipment-image">
                        </a>
                        <p><a href="equipment.php?id=<?php echo $row['id']; ?>"><?php echo $row['equipment_name']; ?></a></p>
                        
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Оборудование не найдено.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>


