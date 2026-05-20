<?php
session_start();

require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ОБНОВЛЕНИЕ КОЛИЧЕСТВА */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_quantity'])) {

    $cart_id = (int) $_POST['cart_id'];
    $quantity = (int) $_POST['quantity'];

    if ($quantity < 1) {
        $quantity = 1;
    }

    $stmt = $pdo->prepare("
        UPDATE cart
        SET quantity = :quantity
        WHERE id = :cart_id
        AND user_id = :user_id
    ");

    $stmt->execute([
        ':quantity' => $quantity,
        ':cart_id' => $cart_id,
        ':user_id' => $user_id
    ]);

    header("Location: cart.php");
    exit();
}

$stmt = $pdo->prepare("
    SELECT books.*, cart.id AS cart_id, cart.quantity
    FROM cart
    JOIN books ON cart.book_id = books.id
    WHERE cart.user_id = :user_id
");

$stmt->execute([
    ':user_id' => $user_id
]);

$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Корзина</title>
    <link rel="stylesheet" href="stylesite.css?v=1702">
</head>

<body>

<header class="header">

    <div class="logo">
        <h1>Книжный мир</h1>
        <p>Открой книгу — открой мир</p>
    </div>

    <nav class="main-nav">
        <ul>
            <li><a href="index.php">Главная</a></li>
            <li><a href="favorites.php">Избранное</a></li>
            <li><a href="cart.php">Корзина</a></li>
            <li><a href="delivery.php">Доставка</a></li>
            <li><a href="profile.php">Личный кабинет</a></li>
            <li><a href="logout.php">Выход</a></li>
        </ul>
    </nav>

</header>

<main class="main-content">

    <section class="banner">
        <div class="banner-text">
            <h2>Корзина</h2>
        </div>
    </section>

    <?php if (count($cart_items) > 0): ?>

        <div class="cart-list">

            <?php foreach ($cart_items as $item): ?>

                <?php
                    $sum = $item['price'] * $item['quantity'];
                    $total += $sum;
                ?>

                <div class="cart-item">

                    <img
                        src="<?php echo htmlspecialchars($item['image']); ?>"
                        alt="<?php echo htmlspecialchars($item['title']); ?>"
                    >

                    <div class="cart-info">

                        <h3>
                            <?php echo htmlspecialchars($item['title']); ?>
                        </h3>

                        <p>
                            <?php echo htmlspecialchars($item['author']); ?>
                        </p>

                        <strong>
                            <?php echo htmlspecialchars($item['price']); ?> ₽
                        </strong>

                    </div>

                    <div class="cart-quantity">

                        <form method="POST" class="quantity-form">

                            <input
                                type="hidden"
                                name="cart_id"
                                value="<?php echo htmlspecialchars($item['cart_id']); ?>"
                            >

                            <label>Количество:</label>

                            <input
                                type="number"
                                name="quantity"
                                value="<?php echo htmlspecialchars($item['quantity']); ?>"
                                min="1"
                                onchange="this.form.submit()"
                            >

                            <input
                                type="hidden"
                                name="update_quantity"
                                value="1"
                            >

                        </form>

                    </div>

                    <div class="cart-sum">
                        <?php echo htmlspecialchars($sum); ?> ₽
                    </div>

                    <div class="cart-buttons">

                        <form action="checkout.php" method="POST">

                            <input
                                type="hidden"
                                name="book_id"
                                value="<?php echo htmlspecialchars($item['id']); ?>"
                            >

                            <button type="submit" class="buy-btn">
                                Купить
                            </button>

                        </form>

                        <form action="remove_cart.php" method="POST">

                            <input
                                type="hidden"
                                name="cart_id"
                                value="<?php echo htmlspecialchars($item['cart_id']); ?>"
                            >

                            <button
                                type="submit"
                                class="remove-cart-btn"
                            >
                                Удалить из корзины
                            </button>

                        </form>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

        <div class="cart-bottom">

            <div class="cart-total">
                <span>Итого:</span>
                <strong><?php echo htmlspecialchars($total); ?> ₽</strong>
            </div>

            <form action="checkout.php" method="POST" class="buy-all-form">

                <input
                    type="hidden"
                    name="buy_all"
                    value="1"
                >

                <button type="submit" class="buy-all-btn">
                    Купить всё
                </button>

            </form>

        </div>

    <?php else: ?>

        <div class="empty-orders">
            Корзина пока пустая.
        </div>

    <?php endif; ?>

</main>

<footer class="footer">

    <div class="footer-content">

        <div class="footer-section about-section">
            <h3>О нас</h3>
            <p>Книжный магазин с 2010 года</p>

            <p class="copyright">
                © 2025 Книжный мир. Все права защищены.
            </p>
        </div>

        <div class="footer-section contacts-section">
            <h3>Контакты</h3>
            <p>Горячая линия: 8-800-123-45-67</p>
            <p>Email: KnizhnyMir@mail.ru</p>
        </div>

    </div>

</footer>

</body>
</html>