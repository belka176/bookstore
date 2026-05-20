<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "bookstore");

if (!$conn) {
    die("Ошибка подключения: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (
        empty($name) ||
        empty($username) ||
        empty($email) ||
        empty($phone) ||
        empty($password) ||
        empty($confirm_password)
    ) {

        $error = "Заполните все поля.";

    } elseif (strlen($username) < 3) {

        $error = "Логин должен содержать минимум 3 символа.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Введите корректный Email.";

    } elseif (strlen($phone) < 10) {

        $error = "Введите корректный номер телефона.";

    } elseif (strlen($password) < 6) {

        $error = "Пароль должен содержать минимум 6 символов.";

    } elseif ($password !== $confirm_password) {

        $error = "Пароли не совпадают.";

    } else {

        $name = mysqli_real_escape_string($conn, $name);
        $username = mysqli_real_escape_string($conn, $username);
        $email = mysqli_real_escape_string($conn, $email);
        $phone = mysqli_real_escape_string($conn, $phone);

        $check_email = mysqli_query($conn, "
            SELECT id 
            FROM users 
            WHERE email='$email'
        ");

        $check_username = mysqli_query($conn, "
            SELECT id 
            FROM users 
            WHERE username='$username'
        ");

        if (mysqli_num_rows($check_email) > 0) {

            $error = "Пользователь с таким Email уже существует.";

        } elseif (mysqli_num_rows($check_username) > 0) {

            $error = "Такой логин уже занят.";

        } else {

            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $query = "
                INSERT INTO users(
                    username,
                    name,
                    email,
                    phone,
                    password,
                    role
                )
                VALUES(
                    '$username',
                    '$name',
                    '$email',
                    '$phone',
                    '$password_hash',
                    'user'
                )
            ";

            if (mysqli_query($conn, $query)) {

                $_SESSION['success_register'] = "Вы успешно зарегистрировались!";

                header("Location: login.php");
                exit();

            } else {

                $error = "Ошибка регистрации: " . mysqli_error($conn);

            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>

    <link rel="stylesheet" href="stylesite.css?v=5003">
</head>

<body class="auth-page">

<div class="auth-box">

    <h1>Регистрация</h1>

    <p class="auth-subtitle">
        Создайте аккаунт в Книжном мире
    </p>

    <?php if(!empty($error)): ?>

        <div class="auth-error">
            <?php echo $error; ?>
        </div>

    <?php endif; ?>

    <form method="POST" class="auth-form">

        <input
            type="text"
            name="name"
            placeholder="Ваше имя"
            value="<?php echo htmlspecialchars($name ?? ''); ?>"
            required
        >

        <input
            type="text"
            name="username"
            placeholder="Логин"
            value="<?php echo htmlspecialchars($username ?? ''); ?>"
            required
        >

        <input
            type="email"
            name="email"
            placeholder="Email"
            value="<?php echo htmlspecialchars($email ?? ''); ?>"
            required
        >

        <input
            type="text"
            name="phone"
            placeholder="Телефон"
            value="<?php echo htmlspecialchars($phone ?? ''); ?>"
            required
        >

        <input
            type="password"
            name="password"
            placeholder="Пароль"
            required
        >

        <input
            type="password"
            name="confirm_password"
            placeholder="Повторите пароль"
            required
        >

        <button type="submit">
            Зарегистрироваться
        </button>

    </form>

    <div class="auth-link">
        Уже есть аккаунт?
        <a href="login.php">Войти</a>
    </div>

</div>

</body>
</html>