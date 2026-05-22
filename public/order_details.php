<?php
session_start();

require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = (int) $_GET['id'];

$stmt = $pdo->prepare("
    SELECT *
    FROM orders
    WHERE id = :order_id
    AND user_id = :user_id
");

$stmt->execute([
    ':order_id' => $order_id,
    ':user_id' => $user_id
]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header("Location: profile.php");
    exit();
}

$stmt = $pdo->prepare("
    SELECT *
    FROM order_items
    WHERE order_id = :order_id
");

$stmt->execute([
    ':order_id' => $order_id
]);

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Детали заказа</title>
    <link rel="stylesheet" href="stylesite.css?v=2100">

</head>

<body>

<header class="header">
    <div class="logo">
        <h1>Книжный мир</h1>
        <p>Открой книгу — открой мир</p>
    </div>
</header>

<main class="order-details-page">

    <div class="order-details-box">

        <h2>Заказ №<?php echo htmlspecialchars($order['id']); ?></h2>

        <p><strong>Дата:</strong> <?php echo htmlspecialchars($order['order_date']); ?></p>
        <p><strong>Сумма:</strong> <?php echo htmlspecialchars($order['total_price']); ?> ₽</p>
        <p><strong>Статус:</strong> <?php echo htmlspecialchars($order['status']); ?></p>

        <h3>Книги в заказе</h3>

        <?php foreach ($items as $item): ?>

            <div class="order-book">
                <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                <p><strong>Автор:</strong> <?php echo htmlspecialchars($item['author']); ?></p>
                <p><strong>Цена:</strong> <?php echo htmlspecialchars($item['price']); ?> ₽</p>
                <p><strong>Количество:</strong> <?php echo htmlspecialchars($item['quantity']); ?></p>
            </div>

        <?php endforeach; ?>

        <a href="profile.php" class="back-btn">Назад в личный кабинет</a>

    </div>

</main>

</body>
</html>