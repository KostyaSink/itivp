<?php
require('db.php');
session_start();

// Проверка, что пользователь авторизован
if (!isset($_SESSION['username'])) {
    echo "<p>Пожалуйста, <a href='login.php'>войдите</a>, чтобы арендовать оборудование.</p>";
    exit();
}

// Обработка бронирования оборудования
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_equipment'])) {
    $equipment_id = mysqli_real_escape_string($con, $_POST['equipment_id']);
    $username = $_SESSION['username'];
    $start_date = mysqli_real_escape_string($con, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($con, $_POST['end_date']);

    $check_query = "SELECT * FROM bookings 
                    WHERE equipment_id = '$equipment_id' 
                    AND (start_date <= '$end_date' AND end_date >= '$start_date')";
    $check_result = mysqli_query($con, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        echo "<p>Ошибка: Оборудование уже забронировано на выбранные даты.</p>";
    } else {
        $insert_query = "INSERT INTO bookings (equipment_id, user_id, username, start_date, end_date, status) 
                         VALUES ('$equipment_id', '$username', '$username', '$start_date', '$end_date', 'pending')";
        
        if (mysqli_query($con, $insert_query)) {
            echo "<p>Бронирование успешно добавлено!</p>";
        } else {
            echo "<p>Ошибка: " . mysqli_error($con) . "</p>";
        }
    }
}

// Обработка отмены бронирования
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_booking'])) {
    $booking_id = mysqli_real_escape_string($con, $_POST['booking_id']);
    $username = $_SESSION['username'];

    // Удаляем бронирование, чтобы пользователь мог снова забронировать
    $delete_query = "DELETE FROM bookings WHERE id='$booking_id' AND username='$username'";
    if (mysqli_query($con, $delete_query)) {
        echo "<p>Бронирование успешно отменено.</p>";
    } else {
        echo "<p>Ошибка при отмене бронирования: " . mysqli_error($con) . "</p>";
    }
}

// Запрос для получения оборудования
if (isset($_GET['id'])) {
    $equipment_id = mysqli_real_escape_string($con, $_GET['id']);
    $query = "SELECT * FROM equipment WHERE id='$equipment_id'";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) > 0) {
        $equipment = mysqli_fetch_assoc($result);
    } else {
        echo "<p>Оборудование не найдено.</p>";
        exit();
    }
} else {
    echo "<p>Неизвестный ID оборудования.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?php echo $equipment['equipment_name']; ?></title>
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
        min-height: 100vh;
        padding: 20px;
    }

    h1, h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #333;
    }

    .equipment-card {
        display: block;
        width: 100%;
        max-width: 400px;
        height: auto;
        border-radius: 10px;
        margin: 20px auto;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .equipment-details {
        background-color: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        text-align: center;
    }

    .equipment-details p {
        font-size: 16px;
        margin-bottom: 10px;
    }

    .equipment-details strong {
        color: #555;
    }

    .rental-form {
        background-color: #fff;
        padding: 20px 30px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 400px;
        margin: 20px auto;
    }

    .rental-form label {
        display: block;
        font-size: 14px;
        color: #555;
        margin-bottom: 8px;
    }

    .rental-form input[type="date"],
    .rental-form input[type="text"] {
        width: 100%;
        padding: 10px;
        font-size: 16px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        transition: all 0.3s ease;
    }

    .rental-form input[type="date"]:focus,
    .rental-form input[type="text"]:focus {
        border-color: #4CAF50;
        outline: none;
    }

    .button {
        display: block;
        width: 100%;
        padding: 12px 0;
        font-size: 16px;
        font-weight: bold;
        color: #fff;
        background-color: #4CAF50;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        margin-top: 10px;
    }

    .button:hover {
        background-color: #45a049;
    }

    .button:active {
        background-color: #388E3C;
    }

    .error-message,
    .success-message {
        text-align: center;
        padding: 10px;
        font-size: 16px;
        margin-bottom: 20px;
        border-radius: 5px;
    }

    .error-message {
        background-color: #f8d7da;
        color: #721c24;
    }

    .success-message {
        background-color: #d4edda;
        color: #155724;
    }

    /* Адаптивность для мобильных устройств */
    @media (max-width: 768px) {
        body {
            padding: 10px;
        }

        .equipment-card {
            width: 100%;
        }

        .rental-form {
            padding: 15px 20px;
        }

        .button {
            font-size: 14px;
        }

        h1, h2 {
            font-size: 22px;
        }

        .rental-form label {
            font-size: 12px;
        }

        .rental-form input[type="date"],
        .rental-form input[type="text"] {
            font-size: 14px;
        }
    }
</style>

    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>
    <div class="equipment-details">
        <h1><?php echo $equipment['equipment_name']; ?></h1>
        <img src="uploads/<?php echo $equipment['image']; ?>" class="equipment-card" alt="Фото оборудования">
        <p><strong>Владелец:</strong> <?php echo $equipment['username']; ?></p>
        <p><strong>Категория:</strong> <?php echo $equipment['category']; ?></p>
        <p><strong>Дата добавления:</strong> <?php echo $equipment['dateadd']; ?></p>
    </div>

    <?php if (isset($error_message)): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
    <?php elseif (isset($success_message)): ?>
        <div class="success-message"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <div class="rental-form">
        <h2>Аренда оборудования</h2>
        <form method="POST" action="">
            <input type="hidden" name="equipment_id" value="<?php echo $equipment['id']; ?>">
            
            <label for="start_date">Дата начала аренды:</label>
            <input type="date" name="start_date" required>
            
            <label for="end_date">Дата окончания аренды:</label>
            <input type="date" name="end_date" required>
            
            <button type="submit" name="book_equipment" class="button">Забронировать</button>
        </form>
    </div>

    <h2>Ваши бронирования</h2>
    <?php 
    // Запрос на получение активных бронирований текущего пользователя
    $username = $_SESSION['username'];
    $bookings_query = "SELECT * FROM bookings 
                       WHERE username = '$username' 
                       AND equipment_id = '$equipment_id' 
                       AND status = 'pending'"; 
    $bookings_result = mysqli_query($con, $bookings_query);

    if (mysqli_num_rows($bookings_result) > 0) {
        while ($booking = mysqli_fetch_assoc($bookings_result)) {
            echo "<div class='booking-item'>";
            echo "<p><strong>Дата начала:</strong> " . htmlspecialchars($booking['start_date']) . "</p>";
            echo "<p><strong>Дата окончания:</strong> " . htmlspecialchars($booking['end_date']) . "</p>";
            echo "<form method='POST' action=''>";
            echo "<input type='hidden' name='booking_id' value='" . $booking['id'] . "'>";
            echo "<button type='submit' name='cancel_booking' class='button cancel-button'>Отменить бронирование</button>";
            echo "</form>";
            echo "</div>";
        }
    } else {
        echo "<p>У вас нет активных бронирований для этого оборудования.</p>";
    }
    ?>

</body>
</html>