<?php
session_start();

require_once 'config/db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("
        SELECT *
        FROM users
        WHERE email = :email
    ");

    $stmt->execute([
        ':email' => $email
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {

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

<?php if (isset($_SESSION['success_register'])): ?>

    <div class="success-message">
        <?php
            echo htmlspecialchars($_SESSION['success_register']);
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

        <?php if ($error): ?>

            <div class="auth-error">
                <?php echo htmlspecialchars($error); ?>
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