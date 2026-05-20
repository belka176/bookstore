<?php
session_start();

require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT books.*
    FROM favorites
    JOIN books ON favorites.book_id = books.id
    WHERE favorites.user_id = :user_id
");

$stmt->execute([
    ':user_id' => $user_id
]);

$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Избранное</title>
    <link rel="stylesheet" href="stylesite.css?v=701">
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
            <h2>Избранное</h2>
        </div>
    </section>

    <?php if (count($favorites) > 0): ?>

        <section class="books-grid">

            <?php foreach ($favorites as $book): ?>

                <div class="book-card">

                    <div class="book-image">
                        <img 
                            src="<?php echo htmlspecialchars($book['image']); ?>" 
                            alt="<?php echo htmlspecialchars($book['title']); ?>"
                        >
                    </div>

                    <div class="book-info">

                        <h3 class="book-title">
                            <?php echo htmlspecialchars($book['title']); ?>
                        </h3>

                        <p class="book-author">
                            <?php echo htmlspecialchars($book['author']); ?>
                        </p>

                        <div class="book-price">
                            <?php echo htmlspecialchars($book['price']); ?> ₽
                        </div>

                        <form action="remove_favorite.php" method="POST">
                            <input 
                                type="hidden" 
                                name="book_id" 
                                value="<?php echo htmlspecialchars($book['id']); ?>"
                            >

                            <button type="submit" class="remove-favorite-btn">
                                Удалить из избранного
                            </button>
                        </form>

                        <form action="add_to_cart.php" method="POST">
                            <input 
                                type="hidden" 
                                name="book_id" 
                                value="<?php echo htmlspecialchars($book['id']); ?>"
                            >

                            <button type="submit" class="add-to-cart">
                                В корзину
                            </button>
                        </form>

                    </div>

                </div>

            <?php endforeach; ?>

        </section>

    <?php else: ?>

        <div class="empty-orders">
            У вас пока нет избранных книг.
        </div>

    <?php endif; ?>

</main>

<footer class="footer">
    <div class="footer-content">

        <div class="footer-section about-section">
            <h3>О нас</h3>
            <p>Книжный магазин с 2010 года</p>
            <p class="copyright">© 2025 Книжный мир. Все права защищены.</p>
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