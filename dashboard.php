<?php
require('db.php');
session_start();
$is_logged_in = isset($_SESSION['username']);
$role = $_SESSION['role'] ?? null;

// Проверка соединения с базой данных
$db_error = "";
if (!$con) {
    $db_error = "Ошибка подключения к базе данных.";
}

// Инициализация переменной поиска
$search_query = '';
$search_results = true; // Переменная для проверки наличия результатов

if (isset($_GET['search']) && $con) {
    $search_query = mysqli_real_escape_string($con, $_GET['search']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
/* Общие стили */
body {
    font-family: 'Arial', sans-serif;
    background-color: #f4f4f4;
    color: #333;
    margin: 0;
    padding: 5px;
}

h1 {
    color: #333;
    font-size: 1.8em;
    margin-bottom: 20px;
}

/* Стили для формы поиска */
.search-form {
    text-align: center;
    margin: 20px 0;
}

.search-form input {
    padding: 10px;
    font-size: 1em;
    width: 250px;
    max-width: 80%;
    border: 2px solid #ccc;
    border-radius: 5px;
    outline: none;
}

.search-form input:focus {
    border-color: #007bff;
}

.search-form button {
    padding: 10px 15px;
    font-size: 1em;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin-left: 10px;
    transition: background-color 0.3s ease;
}

.search-form button:hover {
    background-color: #0056b3;
}

/* Стили для сообщений об ошибках */
.error {
    color: red;
    font-size: 0.9em;
    margin-top: 10px;
}

/* Стили для карточек оборудования */
.equipment-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 30px;
}

.equipment-card {
    background-color: #d99f9f3c;
    border: 1px solid #ddd;

    padding: 15px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.equipment-card img {
    height: 240px; /* Фиксированная высота изображения */
}
.equipment-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

.equipment-card p {
    font-size: 1.1em;
    margin-bottom: 10px;
}

.equipment-card p a {
    text-decoration: none;
    color: #333;
    font-weight: bold;
    transition: color 0.3s ease;
}

.equipment-card p a:hover {
    color: #007bff;
}
.center {
    text-align: center;
    }
    .search-form {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 20px 0; /* Отступы сверху и снизу */
        }
        
        .search-form input {
            margin-right: 10px; /* Отступ между полем ввода и кнопкой */
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="content">

    <?php if ($db_error): ?>
        <p class="error"><?php echo $db_error; ?></p>
    <?php else: ?>
        
        <form method="GET" action="" class="search-form">
            <input type="text" name="search" placeholder="Введите полное название оборудования или владельца" value="<?php echo htmlspecialchars($search_query); ?>" />
            <button type="submit">Поиск</button>
        </form>
        <div class="center">
        <h1>Последнее добавленное оборудование</h1>
        </div>
        <div class="equipment-grid">
            <?php
            // Выполнение запроса только если подключение успешно
            if ($con) {
                // Запрос с фильтром поиска, если введён запрос
                $query = "SELECT id, equipment_name, owner, image FROM equipment ";
                if (!empty($search_query)) {
                    $query .= "WHERE (equipment_name = '$search_query' OR owner = '$search_query') ";
                }
                $query .= "ORDER BY dateadd DESC LIMIT 10";
                
                $result = mysqli_query($con, $query);

                // Проверка наличия результатов
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)): ?>
                        <div class='equipment-card'>
                            <p><a href='equipment.php?id=<?php echo $row['id']; ?>'><?php echo $row['equipment_name']; ?></a></p>
                            
                           <img src='uploads/<?php echo $row['image']; ?>' width='200' height='400' alt='equipment image' class="equipment-image">
                        </div>
                    <?php endwhile;
                } else {

                    echo "<p>Искомое оборудование не найдено</p>";
                }
            } else {
                echo "<p>Не удалось загрузить оборудование из-за ошибки базы данных.</p>";
            }
            ?>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
