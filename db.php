<?php

try {
    $con = new mysqli('localhost', 'root', '', 'equipment_rental');
    if ($con->connect_error) {
        throw new mysqli_sql_exception("Ошибка подключения к базе данных: " . $con->connect_error);
    }
} catch (mysqli_sql_exception $e) {
    $con = null; 
    error_log($e->getMessage()); 
}
?>
