<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Регистрация</title>
    <link rel="stylesheet" href="style.css"/>
    <style>
        .error { color: red; font-size: 0.9em; margin-top: 5px; }
    </style>
</head>
<body>
<?php
    require('db.php');
    include 'navbar.php';
    // Инициализация переменных для сообщений об ошибках
    $username_error = $email_error = $password_error = ""; 

    if (!$con) {
        echo "<p class='error'>Ошибка подключения к базе данных. Пожалуйста, попробуйте позже.</p>";
    } else {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $username = stripslashes($_POST['username']);
            $username = mysqli_real_escape_string($con, $username);
            $email = stripslashes($_POST['email']);
            $email = mysqli_real_escape_string($con, $email);
            $password = stripslashes($_POST['password']);
            $password = mysqli_real_escape_string($con, $password);

            $errors = false;

            // Проверка на пустые поля
            if (empty($username)) {
                $username_error = "Имя пользователя обязательно для заполнения.";
                $errors = true;
            } elseif (strlen($username) < 4 || strlen($username) > 10) {
                $username_error = "Имя пользователя должно быть от 4 до 10 символов.";
                $errors = true;
            } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
                $username_error = "Имя пользователя может содержать только буквы и цифры без пробелов.";
                $errors = true;
            }

            if (empty($email)) {
                $email_error = "Электронная почта обязательна для заполнения.";
                $errors = true;
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $email_error = "Некорректный формат электронной почты.";
                $errors = true;
            }

            if (empty($password)) {
                $password_error = "Пароль обязателен для заполнения.";
                $errors = true;
            } elseif (strlen($password) < 4 || strlen($password) > 16) {
                $password_error = "Пароль должен быть от 4 до 16 символов.";
                $errors = true;
            } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $password)) {
                $password_error = "Пароль пользователя может содержать только буквы и цифры без пробелов.";
                $errors = true;
            }

            // Проверка уникальности имени пользователя и почты
            if (!$errors) {
                $check_user_query = "SELECT * FROM `users` WHERE username='$username' OR email='$email'";
                $check_user_result = mysqli_query($con, $check_user_query);

                if (mysqli_num_rows($check_user_result) > 0) {
                    while ($row = mysqli_fetch_assoc($check_user_result)) {
                        if ($row['username'] == $username) {
                            $username_error = "Это имя пользователя уже занято.";
                        }
                        if ($row['email'] == $email) {
                            $email_error = "Эта электронная почта уже зарегистрирована.";
                        }
                    }
                    $errors = true;
                }
            }

            // Если ошибок нет, добавляем пользователя в базу
            if (!$errors) {
                $query = "INSERT INTO `users` (username, password, email) VALUES ('$username', '" . md5($password) . "', '$email')";
                $result = mysqli_query($con, $query);

                if ($result) {
                    echo "<div class='form'>
                          <h3>Вы успешно зарегистрированы.</h3><br/>
                          <p class='link'>Нажмите здесь, чтобы <a href='login.php'>войти</a></p>
                          </div>";
                } else {
                    echo "<div class='form'><h3>Не удалось зарегистрировать. Пожалуйста, попробуйте снова.</h3></div>";
                }
            }
        }
    }
?>

    <form class="form" action="" method="post" style="margin-top: 20px;">
        <h1 class="login-title">Регистрация</h1>

        <input type="text" class="login-input" name="username" placeholder="Имя пользователя" value="<?php echo isset($username) ? $username : ''; ?>" required />
        <p class="error"><?php echo $username_error; ?></p>

        <input type="text" class="login-input" name="email" placeholder="Электронная почта" value="<?php echo isset($email) ? $email : ''; ?>" required />
        <p class="error"><?php echo $email_error; ?></p>

        <input type="password" class="login-input" name="password" placeholder="Пароль" required />
        <p class="error"><?php echo $password_error; ?></p>

        <input type="submit" name="submit" value="Зарегистрироваться" class="login-button">
      
    </form>
</body>
</html>
