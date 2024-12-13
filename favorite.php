<?php
require('db.php');
session_start();


// Проверяем, авторизован ли текущий пользователь
if (!isset($_SESSION['username'])) {
    echo "<p>Вы должны быть авторизованы, чтобы просматривать избранное.</p>";
    exit();
}

// Определяем пользователя, чьё избранное нужно отобразить
$username = isset($_GET['user']) ? mysqli_real_escape_string($con, $_GET['user']) : $_SESSION['username'];

// Проверяем, существует ли пользователь с ролью renter
$user_query = "SELECT username FROM users WHERE username = '$username' AND role = 'renter' OR role = 'owner'";
$user_result = mysqli_query($con, $user_query);

if (mysqli_num_rows($user_result) == 0) {
    echo "<p>Пользователь с именем $username не найден или не является renter/owner.</p>";
    exit();
}

// Получаем избранные книги пользователя
$favourites_query = "SELECT equipment.id, equipment.equipment_name, equipment.owner, equipment.image 
                     FROM favourites 
                     JOIN equipment ON favourites.equipment_id = equipment.id 
                     WHERE favourites.user_id = '$username'";
$favourites_result = mysqli_query($con, $favourites_query);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Избранное <?php echo htmlspecialchars($username); ?></title>
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
        padding: 20px;
    }

    .content {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    h1 {
        text-align: center;
        margin-bottom: 20px;
        color: #333;
    }

    /* Стили формы поиска */
    .search-form {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
    }

    .search-form input[type="text"] {
        width: 60%;
        max-width: 400px;
        padding: 10px 15px;
        font-size: 16px;
        border: 1px solid #ddd;
        border-radius: 5px 0 0 5px;
    }

    .search-form button {
        padding: 10px 20px;
        font-size: 16px;
        border: none;
        background-color: #4CAF50;
        color: #fff;
        border-radius: 0 5px 5px 0;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .search-form button:hover {
        background-color: #45a049;
    }

    .error {
        text-align: center;
        background-color: #f8d7da;
        color: #721c24;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .equipment-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .equipment-card {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        text-align: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .equipment-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
    }

    .equipment-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }

    .equipment-card p {
        padding: 10px 15px;
        font-size: 16px;
        color: #555;
    }

    .equipment-card p a {
        color: #333;
        text-decoration: none;
        font-weight: bold;
    }

    .equipment-card p a:hover {
        color: #4CAF50;
    }

    .equipment-card strong {
        font-weight: bold;
        color: #555;
    }

    /* Адаптивность */
    @media (max-width: 768px) {
        .search-form input[type="text"] {
            width: 100%;
            border-radius: 5px;
        }

        .search-form button {
            border-radius: 5px;
            margin-top: 10px;
            width: 100%;
        }

        .equipment-grid {
            grid-template-columns: 1fr;
        }
    }
</style>


    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>
    <h1>Избранное оборудование пользователя <?php echo htmlspecialchars($username); ?></h1>
    <div class="equipment-grid">
        <?php if (mysqli_num_rows($favourites_result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($favourites_result)): ?>
                <div class="equipment-card">
                    <a href="equipment.php?id=<?php echo $row['id']; ?>">
                        <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['equipment_name']); ?>" class="equipment-image">
                    </a>
                    <p><a href="equipment.php?id=<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['equipment_name']); ?></a></p>
                    
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Избранного оборудования пока нет.</p>
        <?php endif; ?>
    </div>
</body>
</html>
