<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "bookstore");

if (!$conn) {
    die("Ошибка подключения: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = intval($_GET['id']);

$order_query = mysqli_query($conn, "
    SELECT *
    FROM orders
    WHERE id = '$order_id'
    AND user_id = '$user_id'
");

if (!$order_query || mysqli_num_rows($order_query) == 0) {
    header("Location: profile.php");
    exit();
}

$order = mysqli_fetch_assoc($order_query);

$items_query = mysqli_query($conn, "
    SELECT *
    FROM order_items
    WHERE order_id = '$order_id'
");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Детали заказа</title>
    <link rel="stylesheet" href="stylesite.css?v=2100">

    <style>
        .order-details-page {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .order-details-box {
            background: #19573a;
            color: white;
            padding: 35px;
            border-radius: 18px;
        }

        .order-book {
            background: #f0f6ee;
            color: #19573a;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 15px;
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            background: #2a7d5c;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
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

        <h2>Заказ №<?php echo $order['id']; ?></h2>

        <p><strong>Дата:</strong> <?php echo $order['order_date']; ?></p>
        <p><strong>Сумма:</strong> <?php echo $order['total_price']; ?> ₽</p>
        <p><strong>Статус:</strong> <?php echo $order['status']; ?></p>

        <h3>Книги в заказе</h3>

        <?php while($item = mysqli_fetch_assoc($items_query)): ?>

            <div class="order-book">
                <h3><?php echo $item['title']; ?></h3>
                <p><strong>Автор:</strong> <?php echo $item['author']; ?></p>
                <p><strong>Цена:</strong> <?php echo $item['price']; ?> ₽</p>
                <p><strong>Количество:</strong> <?php echo $item['quantity']; ?></p>
            </div>

        <?php endwhile; ?>

        <a href="profile.php" class="back-btn">Назад в личный кабинет</a>

    </div>

</main>

</body>
</html>