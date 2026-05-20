<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$book_id = $_POST['book_id'] ?? '';
$buy_all = $_POST['buy_all'] ?? '';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Оформление заказа</title>
    <link rel="stylesheet" href="stylesite.css?v=1700">
</head>

<body>

<header class="header">
    <div class="logo">
        <h1>Книжный мир</h1>
        <p>Открой книгу — открой мир</p>
    </div>
</header>

<main class="main-content">

    <section class="banner">
        <h2>Оформление заказа</h2>
    </section>

    <form action="buy.php" method="POST" class="checkout-box">

        <?php if ($book_id): ?>
            <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
        <?php endif; ?>

        <?php if ($buy_all): ?>
            <input type="hidden" name="buy_all" value="1">
        <?php endif; ?>

        <label>Способ доставки</label>
        <select name="delivery_method" required>
            <option value="">Выберите доставку</option>
            <option value="Курьерская доставка">Курьерская доставка — 250 ₽</option>
            <option value="Пункт выдачи">Пункт выдачи — 150 ₽</option>
            <option value="Почта России">Почта России — от 300 ₽</option>
        </select>

        <label>Адрес доставки</label>
        <input type="text" name="address" placeholder="Введите адрес" required>

        <label>Телефон</label>
        <input type="text" name="phone" placeholder="Введите телефон" required>

        <label>Комментарий к заказу</label>
        <textarea name="comment" placeholder="Например: позвонить перед доставкой"></textarea>

        <button type="submit" class="buy-all-btn">
            Подтвердить заказ
        </button>

    </form>

</main>

</body>
</html>