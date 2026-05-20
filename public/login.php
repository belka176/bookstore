<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "bookstore");

if (!$conn) {
    die("Ошибка подключения: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {

        $user = mysqli_fetch_assoc($result);

        if (
            $password == $user['password'] ||
            password_verify($password, $user['password'])
        ) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['success_login'] = "Вы успешно авторизировались!";

            header("Location: index.php");
            exit();

        } else {

            $error = "Неверный пароль";

        }

    } else {

        $error = "Пользователь не найден";

    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
    <link rel="stylesheet" href="stylesite.css?v=502">
</head>

<body>

<?php if(isset($_SESSION['success_register'])): ?>

    <div class="success-message">
        <?php
            echo $_SESSION['success_register'];
            unset($_SESSION['success_register']);
        ?>
    </div>

<?php endif; ?>

<div class="auth-page">

    <div class="auth-box">

        <h1>Вход</h1>

        <p class="auth-subtitle">
            Добро пожаловать в Книжный мир
        </p>

        <?php if($error): ?>

            <div class="auth-error">
                <?php echo $error; ?>
            </div>

        <?php endif; ?>

        <form method="POST" class="auth-form">

            <input
                type="email"
                name="email"
                placeholder="Email"
                required
            >

            <input
                type="password"
                name="password"
                placeholder="Пароль"
                required
            >

            <button type="submit">
                Войти
            </button>

        </form>

        <div class="auth-link">
            Нет аккаунта?
            <a href="register.php">Зарегистрироваться</a>
        </div>

    </div>

</div>

</body>
</html>