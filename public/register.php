<?php
session_start();

require_once 'config/db.php';

$error = "";

$name = "";
$username = "";
$email = "";
$phone = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

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

        $stmt = $pdo->prepare("
            SELECT id
            FROM users
            WHERE email = :email
        ");

        $stmt->execute([
            ':email' => $email
        ]);

        $check_email = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("
            SELECT id
            FROM users
            WHERE username = :username
        ");

        $stmt->execute([
            ':username' => $username
        ]);

        $check_username = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($check_email) {

            $error = "Пользователь с таким Email уже существует.";

        } elseif ($check_username) {

            $error = "Такой логин уже занят.";

        } else {

            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users (
                    username,
                    name,
                    email,
                    phone,
                    password,
                    role
                )
                VALUES (
                    :username,
                    :name,
                    :email,
                    :phone,
                    :password,
                    'user'
                )
            ");

            $stmt->execute([
                ':username' => $username,
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':password' => $password_hash
            ]);

            $_SESSION['success_register'] = "Вы успешно зарегистрировались!";

            header("Location: login.php");
            exit();
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

    <?php if (!empty($error)): ?>

        <div class="auth-error">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>

    <form method="POST" class="auth-form">

        <input
            type="text"
            name="name"
            placeholder="Ваше имя"
            value="<?php echo htmlspecialchars($name); ?>"
            required
        >

        <input
            type="text"
            name="username"
            placeholder="Логин"
            value="<?php echo htmlspecialchars($username); ?>"
            required
        >

        <input
            type="email"
            name="email"
            placeholder="Email"
            value="<?php echo htmlspecialchars($email); ?>"
            required
        >

        <input
            type="text"
            name="phone"
            placeholder="Телефон"
            value="<?php echo htmlspecialchars($phone); ?>"
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