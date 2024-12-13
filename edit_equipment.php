<?php
// Подключение к базе данных
require('db.php');
session_start();

$equipment_file_error = $image_error = ""; // Переменные для ошибок

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$role = $_SESSION['role']; // Получаем роль текущего пользователя


if (isset($_GET['id'])) {
    $equipment_id = mysqli_real_escape_string($con, $_GET['id']);

    // Проверяем права доступа
    if ($role === 'admin') {
        $query = "SELECT * FROM equipment WHERE id='$equipment_id'";
    } else {
        $query = "SELECT * FROM equipment WHERE id='$equipment_id' AND username='$username'";
    }

    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) > 0) {
        $equipment = mysqli_fetch_assoc($result);
    } else {
        echo "<p>У вас нет прав на редактирование этого оборудования.</p>";
        exit();
    }
}

if (isset($_POST['update_equipment'])) {
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
            $equipment_file_error = "Ошибка загрузки файла описания оборудования. Проверьте доступность ресурса.";
            $errors = true;
        } else {
            $query = "UPDATE equipment SET equipment_name='$equipment_name', owner='$owner', category='$category', link='$equipmentTarget' WHERE id='$equipment_id'";
        }
    } else {
        $query = "UPDATE equipment SET equipment_name='$equipment_name', owner='$owner', category='$category' WHERE id='$equipment_id'";
    }

    // Проверка формата файла изображения
    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        $imageType = pathinfo($image, PATHINFO_EXTENSION);
        $imageTarget = "uploads/" . basename($image);

        if (!in_array($imageType, ['png', 'jpg'])) {
            $image_error = "Допустимые форматы изображения: .png, .jpg";
            $errors = true;
        } elseif (!move_uploaded_file($_FILES['image']['tmp_name'], $imageTarget)) {
            $image_error = "Ошибка загрузки изображения. Проверьте доступность ресурса.";
            $errors = true;
        } else {
            $query = "UPDATE equipment SET image='$image' WHERE id='$equipment_id'";
        }
    }

    // Выполнение запроса на обновление
    if (!$errors && mysqli_query($con, $query)) {
        header("Location: owner_instrument.php?message=equipment_updated");
        exit();
    } elseif (!$errors) {
        echo "<p>Не удалось обновить информацию об оборудовании.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование</title>
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

    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
    <h1>Редактирование</h1>
    <form class="form" method="POST" enctype="multipart/form-data">
        <label>Название оборудования:</label>
        <input type="text" name="equipment_name" value="<?php echo $equipment['equipment_name']; ?>" required><br>

        <label>Арендодатель:</label>
        <input type="text" name="owner" value="<?php echo $equipment['owner']; ?>" required><br>

        <label>Категория:</label>
        <input type="text" name="category" value="<?php echo $equipment['category']; ?>" required><br>

        <label>Обновить файл описания оборудования:</label>
        <input type="file" name="equipment_file"><br>
        <p class="error"><?php echo $equipment_file_error; ?></p>

        <label>Обновить изображение:</label>
        <input type="file" name="image"><br>
        <p class="error"><?php echo $image_error; ?></p>

        <input type="submit" name="update_equipment" value="Сохранить изменения">
    </form>
</body>
</html>
