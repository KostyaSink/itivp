<?php

require('db.php');
session_start();
include 'navbar.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];

if (isset($_GET['id'])) {
    $equipment_id = mysqli_real_escape_string($con, $_GET['id']);


    if ($role === 'moderator' || $role === 'admin') {
        $query = "DELETE FROM equipment WHERE id='$equipment_id'";
    } else {
        $query = "DELETE FROM equipment WHERE id='$equipment_id' AND username='$username'";
    }

    if (mysqli_query($con, $query)) {
        header("Location: owner_instrument.php?message=equipment_deleted");
        exit();
    } else {
        echo "<p>Не удалось удалить.</p>";
    }
}
?>
