<?php
require('db.php');
session_start();


if (isset($_GET['id'])) {
    $equipment_id = mysqli_real_escape_string($con, $_GET['id']);


    $query = "SELECT * FROM equipment WHERE id='$equipment_id'";
    $result = mysqli_query($con, $query);

    // Проверяем, вернулся ли результат
    if (mysqli_num_rows($result) > 0) {
        $equipment = mysqli_fetch_assoc($result);  
        $equipmentFilePath = $equipment['link']; 
    } else {
        echo "<p>equipment not found</p>";
        exit();
    }
} else {
    echo "<p>Incorrect equipment ID</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?php echo $equipment['equipment_name']; ?> - Reading</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>
    <h1><?php echo $equipment['equipment_name']; ?></h1>
    <img src="uploads/<?php echo $equipment['image']; ?>" width="300" height="400">
    <?php
    // Проверяем тип файла (текст или PDF) и выводим содержимое
    $fileExtension = pathinfo($equipmentFilePath, PATHINFO_EXTENSION);

    // Если файл - текстовый, открываем его и выводим содержимое
    if ($fileExtension == 'txt') {
        $equipmentContent = file_get_contents($equipmentFilePath);
        echo "<pre>" . htmlspecialchars($equipmentContent) . "</pre>"; // Безопасный вывод содержимого
    } elseif ($fileExtension == 'pdf') {
        // Если это PDF, встраиваем его с помощью iframe
        echo "<iframe src='$equipmentFilePath' width='600' height='800'></iframe>";
    } else {
        echo "<p>Unsupported file format</p>";
    }
    ?>
</body>
</html>

