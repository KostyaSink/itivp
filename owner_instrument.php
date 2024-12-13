<?php
session_start();
require('db.php');

// Проверяем права доступа
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];

$equipment_file_error = $image_error = "";

if (isset($_POST['add_equipment'])) {
    $equipment_name = mysqli_real_escape_string($con, $_POST['equipment_name']);
    $owner = mysqli_real_escape_string($con, $_POST['owner']);
    $category = mysqli_real_escape_string($con, $_POST['category']);
    $errors = false;

    if (!empty($_FILES['equipment_file']['name'])) {
        $equipmentFile = $_FILES['equipment_file']['name'];
        $equipmentFileType = pathinfo($equipmentFile, PATHINFO_EXTENSION);
        $equipmentTarget = "uploads/equipments/" . basename($equipmentFile);

        if (!in_array($equipmentFileType, ['txt', 'pdf'])) {
            $equipment_file_error = "Допустимые форматы файла описания оборудования: .txt, .pdf";
            $errors = true;
        } elseif (!move_uploaded_file($_FILES['equipment_file']['tmp_name'], $equipmentTarget)) {
            $equipment_file_error = "Ошибка загрузки файла описания оборудования.";
            $errors = true;
        }
    } else {
        $equipment_file_error = "Файл описания оборудования обязателен для загрузки.";
        $errors = true;
    }

    // Проверка и загрузка изображения
    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        $imageType = pathinfo($image, PATHINFO_EXTENSION);
        $imageTarget = "uploads/" . basename($image);

        if (!in_array($imageType, ['png', 'jpg'])) {
            $image_error = "Допустимые форматы изображения: .png, .jpg";
            $errors = true;
        } elseif (!move_uploaded_file($_FILES['image']['tmp_name'], $imageTarget)) {
            $image_error = "Ошибка загрузки изображения.";
            $errors = true;
        }
    } else {
        $image_error = "Изображение обязательно для загрузки.";
        $errors = true;
    }

    if (!$errors) {
        $query = "INSERT INTO equipment (equipment_name, username, image, category, link, owner) 
                  VALUES ('$equipment_name',  '$username', '$image', '$category', '$equipmentTarget', '$owner')";
        mysqli_query($con, $query);
        
        header("Location: owner_instrument.php?message=equipment_added");
        exit();
    }
}

// Пагинация
$limit = 3; // Количество записей на одной странице
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Подсчет общего количества записей
$count_query = "SELECT COUNT(*) AS total FROM equipment";
if ($role !== 'admin') {
    $count_query .= " WHERE username = '$username'";
}
$count_result = mysqli_query($con, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $limit);

// Запрос для получения записей с учетом лимита и смещения
$query = "SELECT * FROM equipment";
if ($role !== 'admin') {
    $query .= " WHERE username = '$username'";
}
$query .= " LIMIT $offset, $limit";
$result = mysqli_query($con, $query);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Owner Instrument</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        /* Основные стили для всей страницы */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        h1, h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .form {
            width: 50%;
            margin: 30px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .form input[type="text"],
        .form input[type="file"],
        .form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .form input[type="submit"] {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #5cb85c;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .form input[type="submit"]:hover {
            background-color: #4cae4c;
        }

        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
            display: block;
        }

        /* Стили для таблицы */
        table {
            width: 90%;
            margin: 30px auto;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        table th, table td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #5cb85c;
            color: #fff;
            text-transform: uppercase;
            font-weight: bold;
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        table img {
            width: 50px;
            height: auto;
            border-radius: 5px;
        }

        table a {
            text-decoration: none;
            color: #5cb85c;
            font-weight: bold;
            transition: color 0.3s;
        }

        table a:hover {
            color: #4cae4c;
        }

        /* Стили для кнопок действий */
        a.button {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 10px;
            background-color: #5cb85c;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            transition: background-color 0.3s;
        }

        a.button:hover {
            background-color: #4cae4c;
        }

        a.button-danger {
            background-color: #d9534f;
        }

        a.button-danger:hover {
            background-color: #c9302c;
        }

        /* Утилиты */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }
       
    .pagination {
        display: flex;
        justify-content: center; /* Центрирует пагинацию */
        margin-top: 20px; /* Отступ сверху */
    }
    .pagination a {
        margin: 0 5px;
        padding: 8px 12px;
        text-decoration: none;
        color: #000;
        border: 1px solid #ccc;
        border-radius: 4px;
        transition: background-color 0.3s;
    }
    .pagination a:hover {
        background-color: #ddd; /* Подсветка при наведении */
    }
    .pagination a.active {
        font-weight: bold;
        background-color: #007bff;
        color: #fff;
        border-color: #007bff;
    }

    
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
    <form class="form" action="owner_instrument.php" method="POST" enctype="multipart/form-data">
        <h1>Добавить новое оборудование</h1>
        <label>Название оборудования:</label>
        <input type="text" name="equipment_name" required><br>

        <label>Арендодатель:</label>
        <input type="text" name="owner" required><br>

        <label>Категория:</label>
        <input type="text" name="category" required><br>

        <label>Описание оборудования:</label>
        <input type="file" name="equipment_file" required><br>
        <p class="error"><?php echo $equipment_file_error; ?></p>

        <label>Изображение:</label>
        <input type="file" name="image" required><br>
        <p class="error"><?php echo $image_error; ?></p>

        <input type="submit" name="add_equipment" value="Добавить оборудование">
    </form>

    <h2>Ваше оборудование</h2>
    <table>
        <tr>
            <th>Название оборудования</th>
            <th>Арендодатель</th>
            <th>Категория</th>
            <th>Изображение</th>
            <th>Действия</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><a href='equipment.php?id=<?php echo $row['id']; ?>'><?php echo $row['equipment_name']; ?></a></td>
                <td><?php echo $row['owner']; ?></td>
                <td><?php echo $row['category']; ?></td>
                <td><img src='uploads/<?php echo $row['image']; ?>' width='50'></td>
                <td>
                    <?php if ($role === 'admin' || $row['username'] === $username): ?>
                        <a href="edit_equipment.php?id=<?php echo $row['id']; ?>">Редактировать</a> |
                        <a href='delete_equipment.php?id=<?php echo $row['id']; ?>' onclick="return confirm('Вы уверены, что хотите удалить это оборудование?');">Удалить</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="owner_instrument.php?page=<?php echo $i; ?>" class="<?php if ($i == $page) echo 'active'; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
</body>
</html>

