<?php
require('db.php');
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $equipment_id = mysqli_real_escape_string($con, $_POST['equipment_id']);
    $user_id = $_SESSION['username'];
    $username = $_SESSION['username'];
    $comment = mysqli_real_escape_string($con, trim($_POST['comment']));

    if (empty($comment)) {
        echo "<p>Комментарий не может быть пустым</p>";
        exit();
    }

    $query = "INSERT INTO comments (equipment_id, user_id, username, comment) 
              VALUES ('$equipment_id', '$user_id', '$username', '$comment')";
    if (mysqli_query($con, $query)) {
        header("Location: equipment.php?id=$equipment_id");
        exit();
    } else {
        echo "<p>Ошибка добавления комментария: " . mysqli_error($con) . "</p>";
    }
}
?>

