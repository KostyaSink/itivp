<?php
require('db.php');
session_start();


if (isset($_GET['id'])) {
    $equipment_id = mysqli_real_escape_string($con, $_GET['id']);
    
    $query = "SELECT *, 
              (SELECT COALESCE(average_rating, 0) FROM equipment WHERE id = '$equipment_id') AS avg_rating 
              FROM equipment WHERE id='$equipment_id'";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) > 0) {
        $equipment = mysqli_fetch_assoc($result);
    } else {
        echo "<p>Оборудование не найдено</p>";
        exit();
    }

    $comments_query = "SELECT c.comment, c.created_at, u.username 
                       FROM comments c
                       JOIN users u ON c.user_id = u.username
                       WHERE c.equipment_id = '$equipment_id'
                       ORDER BY c.created_at DESC";
    $comments_result = mysqli_query($con, $comments_query);

    $favourites_query = "SELECT u.username 
                         FROM favourites f
                         JOIN users u ON f.user_id = u.username
                         WHERE f.equipment_id = '$equipment_id'";
    $favourites_result = mysqli_query($con, $favourites_query);

    // Запрос для получения текущих бронирований
    $bookings_query = "SELECT username, start_date, end_date FROM bookings 
                       WHERE equipment_id = '$equipment_id' ORDER BY start_date";
    $bookings_result = mysqli_query($con, $bookings_query);

    // Проверка, является ли текущий пользователь владельцем оборудования
    $is_owner = (isset($_SESSION['username']) && isset($_SESSION['role']) && 
                 $_SESSION['username'] == $equipment['username'] && $_SESSION['role'] == 'owner');

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
        /* Основные стили для всей страницы */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        h1, h2 {
            text-align: center;
            color: #222;
            margin-bottom: 20px;
            font-size: 28px;
        }

        p {
            font-size: 18px;
            margin-bottom: 15px;
            line-height: 1.8;
        }

        .equipment-card {
            display: block;
            width: 250px;
            height: auto;
            margin: 20px auto;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .button {
            display: inline-block;
            padding: 12px 25px;
            background: linear-gradient(45deg, #4a90e2, #357ABD);
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            text-align: center;
            transition: background 0.3s, transform 0.2s;
            font-size: 16px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            margin: 10px 5px;
        }

        .button:hover {
            background: linear-gradient(45deg, #357ABD, #285B9B);
            transform: scale(1.05);
        }

        .favourites-section, .bookings-section, .comments-section {
            width: 90%;
            margin: 20px auto;
            background-color: #fff;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        ul {
            list-style: none;
            padding: 0;
        }

        ul li {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }

        ul li:last-child {
            border-bottom: none;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
        }

        table th, table td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }

        table th {
            background-color: #4a90e2;
            color: #fff;
            text-transform: uppercase;
            font-weight: bold;
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        table tr:hover {
            background-color: #e8f4ff;
        }

        .comment {
            background-color: #f9f9f9;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #4a90e2;
            border-radius: 8px;
        }

        .comment p {
            margin: 5px 0;
        }

        .comment-date {
            font-size: 12px;
            color: #777;
        }

        .form {
            width: 90%;
            margin: 20px auto;
            background-color: #fff;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .form textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .form button[type="submit"] {
            display: block;
            width: 100%;
            padding: 12px;
            background: linear-gradient(45deg, #4a90e2, #357ABD);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }

        .form button[type="submit"]:hover {
            background: linear-gradient(45deg, #357ABD, #285B9B);
            transform: scale(1.05);
        }

        .alert {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px 20px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            text-align: center;
        }

        a {
            color: #4a90e2;
            text-decoration: none;
            transition: color 0.3s;
        }

        a:hover {
            color: #357ABD;
        }

        .center-text {
        text-align: center;
    }
    .center-button {
        text-align: center; /* Центрируем текст внутри контейнера */
    }
    </style>


    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>
    <h1><?php echo $equipment['equipment_name']; ?></h1>
<div class="center-text">
    <p><strong>Владелец:</strong> <?php echo $equipment['owner']; ?></p>
    <p><strong>Категория:</strong> <?php echo $equipment['category']; ?></p>
    <p><strong>Дата добавления:</strong> <?php echo $equipment['dateadd']; ?></p>
    <p><strong>Добавлено пользователем:</strong> <?php echo $equipment['username']; ?></p>
    <p><strong>Средняя оценка:</strong> <?php echo round($equipment['avg_rating'], 2); ?></p>
</div>
    <img src="uploads/<?php echo $equipment['image']; ?>" class="equipment-card">
    
    <form method="POST" action="add_to_favourites.php">
        <input type="hidden" name="equipment_id" value="<?php echo $equipment['id']; ?>">
        
        <div class="center-button">
    <button type="submit" class="button">Добавить в избранное</button>
<       </div>
    </form>
    <div class="center-button">
    <a href="check.php?id=<?php echo $equipment['id']; ?>" class="button">Читать описание оборудования</a>
    </div>
<?php if (isset($_SESSION['username']) && $_SESSION['role'] == 'renter') : ?>
    <div class="center-button">
    <a href="add_equipment.php?id=<?php echo $equipment['id']; ?>" class="button">Аренда оборудования</a>
    </div>
    <form method="POST" action="rate_equipment.php">
        <input type="hidden" name="equipment_id" value="<?php echo $equipment['id']; ?>">
        <div class="center-button">
        <button type="submit" class="button">Оценить</button>
        <label for="rating">Оцените оборудование:</label>
        <select name="rating" id="rating" required>
            <?php for ($i = 1; $i <= 10; $i++) : ?>
                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
            <?php endfor; ?>
        </select>
        </div>
    </form>
<?php endif; ?>

<?php if ($is_owner) : ?>
    <h2>Пользователи, добавившие оборудование в избранное:</h2>
    <div class="favourites-section">
        <?php if (mysqli_num_rows($favourites_result) > 0) : ?>
<ul>
    <?php while ($favourite = mysqli_fetch_assoc($favourites_result)) : ?>
        <li><?php echo htmlspecialchars($favourite['username']); ?></li>
    <?php endwhile; ?>
</ul>
<?php else : ?>
<p>Никто еще не добавил это оборудование в избранное.</p>
<?php endif; ?>
</div>
<h2>Текущие бронирования:</h2>
<div class="bookings-section">
    <?php if (mysqli_num_rows($bookings_result) > 0) : ?>
        <table>
            <tr>
                <th>Пользователь</th>
                <th>Дата начала</th>
                <th>Дата окончания</th>
            </tr>
            <?php while ($booking = mysqli_fetch_assoc($bookings_result)) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($booking['username']); ?></td>
                    <td><?php echo date("d.m.Y", strtotime($booking['start_date'])); ?></td>
                    <td><?php echo date("d.m.Y", strtotime($booking['end_date'])); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else : ?>
        <p>Нет текущих бронирований для этого оборудования.</p>
    <?php endif; ?>
</div>
<?php endif; ?>

<h2>Комментарии</h2>
<div class="comments-section">
    <?php if (mysqli_num_rows($comments_result) > 0) : ?>
        <?php while ($comment = mysqli_fetch_assoc($comments_result)) : ?>
            <div class="comment">
                <p><strong><?php echo $comment['username']; ?>:</strong></p>
                <p><?php echo htmlspecialchars($comment['comment']); ?></p>
                <p class="comment-date"><?php echo date("d.m.Y H:i", strtotime($comment['created_at'])); ?></p>
            </div>
        <?php endwhile; ?>
    <?php else : ?>
        <p>Комментариев пока нет. Будьте первым!</p>
    <?php endif; ?>
</div>
<?php if (isset($_SESSION['username'])) : ?>
    <form method="POST" action="add_comment.php">
        <input type="hidden" name="equipment_id" value="<?php echo $equipment['id']; ?>">
        <label for="comment">Ваш комментарий:</label><br>
        <textarea name="comment" id="comment" rows="4" required></textarea><br>
        <button type="submit" class="button">Отправить</button>
    </form>
<?php else : ?>
    <p><a href="login.php">Войдите</a>, чтобы оставить комментарий.</p>
<?php endif; ?>

</body>
</html>

