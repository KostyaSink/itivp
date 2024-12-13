<?php
require('db.php');
session_start();

if ($_SESSION['role'] !== 'renter') {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $equipment_id = mysqli_real_escape_string($con, $_POST['equipment_id']);
    $user_id = $_SESSION['username'];
    $rating = intval($_POST['rating']);

    if ($rating < 1 || $rating > 10) {
        echo "<p>Недопустимая оценка</p>";
        exit();
    }

    // Проверяем, есть ли уже оценка пользователя
    $query = "SELECT * FROM ratings WHERE equipment_id = '$equipment_id' AND user_id = '$user_id'";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) > 0) {
        // Обновляем существующую оценку
        $query = "UPDATE ratings SET rating = '$rating' WHERE equipment_id = '$equipment_id' AND user_id = '$user_id'";
    } else {
        // Вставляем новую оценку
        $query = "INSERT INTO ratings (equipment_id, user_id, rating) VALUES ('$equipment_id', '$user_id', '$rating')";
    }
    mysqli_query($con, $query);

    // Пересчитываем среднюю оценку
    $query = "UPDATE equipment b
              SET average_rating = (
                  SELECT AVG(rating) FROM ratings r WHERE r.equipment_id = b.id
              )
              WHERE b.id = '$equipment_id'";
    mysqli_query($con, $query);

    header("Location: equipment.php?id=$equipment_id");
    exit();
}
?>
