<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8"/>
    <title>Вход</title>
    <link rel="stylesheet" href="style.css"/>
    <style>
        .error { color: red; font-size: 0.9em; margin-top: 5px; }
    </style>
</head>
<body>
<?php
require('db.php');
session_start();
include 'navbar.php';
// Инициализация переменных для сообщений об ошибках
$username_error = $password_error = $login_error = "";

if (!$con) {
    echo "<p class='error'>Ошибка подключения к базе данных. Пожалуйста, попробуйте позже.</p>";
} else {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = stripslashes($_POST['username']);
        $username = mysqli_real_escape_string($con, $username);
        $password = stripslashes($_POST['password']);
        $password = mysqli_real_escape_string($con, $password);

        $errors = false;

        // Валидация полей
        if (empty($username)) {
            $username_error = "Имя пользователя обязательно.";
            $errors = true;
        }
        if (empty($password)) {
            $password_error = "Пароль обязателен.";
            $errors = true;
        }

        if (!$errors) {
            // Поиск пользователя в базе данных
            $query = "SELECT username, role FROM `users` 
                      WHERE username='$username' AND password='" . md5($password) . "'";
            $result = mysqli_query($con, $query);

            if (mysqli_num_rows($result) == 1) {
                // Получение данных пользователя
                $user = mysqli_fetch_assoc($result);

                // Сохранение данных в сессии
                session_start();
                $_SESSION['user_id'] = $user['id']; // Сохраняем ID пользователя
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role']; // Сохраняем роль пользователя

                // Перенаправление на главную страницу
                header("Location: dashboard.php");
                exit();
            } else {
                $login_error = "Неверное имя пользователя или пароль.";
            }
        }
    }
}
?>

<form class="form" method="post" name="login" style="margin-top: 20px;">
    <h1 class="login-title">Вход</h1>

    <input type="text" class="login-input" name="username" placeholder="Имя пользователя" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" autofocus="true"/>
    <p class="error"><?php echo $username_error; ?></p>

    <input type="password" class="login-input" name="password" placeholder="Пароль"/>
    <p class="error"><?php echo $password_error; ?></p>

    <input type="submit" value="Войти" name="submit" class="login-button"/>
    <p class="error"><?php echo $login_error; ?></p>

    
    
</form>
</body>
</html>
