<?php
require('db.php');
session_start();
include 'navbar.php';

if (!isset($_SESSION['username'])) {
    echo "Вы должны быть авторизованы, чтобы добавлять оборудование в избранное.";
    exit();
}

$username = $_SESSION['username']; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['equipment_id'])) {
    $equipment_id = mysqli_real_escape_string($con, $_POST['equipment_id']);


    $check_query = "SELECT * FROM favourites WHERE user_id='$username' AND equipment_id='$equipment_id'";
    $check_result = mysqli_query($con, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        echo "Это оборудование уже в избранном.";
    } else {
        $insert_query = "INSERT INTO favourites (user_id, equipment_id) VALUES ('$username', '$equipment_id')";
        if (mysqli_query($con, $insert_query)) {
            echo "Оборудование успешно добавлено в избранное.";
        } else {
            echo "Ошибка при добавлении оборудования.";
        }
    }
} else {
    echo "Неверный запрос.";
}
?>
