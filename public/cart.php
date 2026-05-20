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

/* ОБНОВЛЕНИЕ КОЛИЧЕСТВА */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_quantity'])) {

    $cart_id = intval($_POST['cart_id']);
    $quantity = intval($_POST['quantity']);

    if ($quantity < 1) {
        $quantity = 1;
    }

    mysqli_query($conn, "
        UPDATE cart
        SET quantity='$quantity'
        WHERE id='$cart_id'
        AND user_id='$user_id'
    ");

    header("Location: cart.php");
    exit();
}

$sql = "
    SELECT books.*, cart.id AS cart_id, cart.quantity
    FROM cart
    JOIN books ON cart.book_id = books.id
    WHERE cart.user_id = '$user_id'
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Ошибка запроса: " . mysqli_error($conn));
}

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

    <?php if (mysqli_num_rows($result) > 0): ?>

        <div class="cart-list">

            <?php while($item = mysqli_fetch_assoc($result)): ?>

                <?php
                    $sum = $item['price'] * $item['quantity'];
                    $total += $sum;
                ?>

                <div class="cart-item">

                    <img 
                        src="<?php echo $item['image']; ?>" 
                        alt="<?php echo $item['title']; ?>"
                    >

                    <div class="cart-info">

                        <h3>
                            <?php echo $item['title']; ?>
                        </h3>

                        <p>
                            <?php echo $item['author']; ?>
                        </p>

                        <strong>
                            <?php echo $item['price']; ?> ₽
                        </strong>

                    </div>

                    <div class="cart-quantity">

                        <form method="POST" class="quantity-form">

                            <input 
                                type="hidden" 
                                name="cart_id" 
                                value="<?php echo $item['cart_id']; ?>"
                            >

                            <label>Количество:</label>

                            <input
                                type="number"
                                name="quantity"
                                value="<?php echo $item['quantity']; ?>"
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
                        <?php echo $sum; ?> ₽
                    </div>

                    <div class="cart-buttons">

                        <form action="checkout.php" method="POST">

                            <input 
                                type="hidden" 
                                name="book_id" 
                                value="<?php echo $item['id']; ?>"
                            >

                            <button type="submit" class="buy-btn">
                                Купить
                            </button>

                        </form>

                        <form action="remove_cart.php" method="POST">

                            <input 
                                type="hidden" 
                                name="cart_id" 
                                value="<?php echo $item['cart_id']; ?>"
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

            <?php endwhile; ?>

        </div>

        <div class="cart-bottom">

            <div class="cart-total">
                <span>Итого:</span>
                <strong><?php echo $total; ?> ₽</strong>
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