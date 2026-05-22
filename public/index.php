<?php
session_start();

require_once 'config/db.php';

$is_admin = false;

if (isset($_SESSION['user_id'])) {

    $stmt = $pdo->prepare("
        SELECT role
        FROM users
        WHERE id = :id
    ");

    $stmt->execute([
        ':id' => $_SESSION['user_id']
    ]);

    $admin_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin_user && $admin_user['role'] === 'admin') {
        $is_admin = true;
    }
}

$sections = [
    "Популярное" => "popular",
    "Новинки" => "new",
    "Эксклюзивно для книжного мира" => "exclusive"
];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Книжный мир</title>

    <link rel="stylesheet" href="stylesite.css?v=9999">

</head>

<body>

<?php if (isset($_SESSION['success_login'])): ?>

    <div class="success-message">
        <?php
            echo htmlspecialchars($_SESSION['success_login']);
            unset($_SESSION['success_login']);
        ?>
    </div>

<?php endif; ?>

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

            <?php if (isset($_SESSION['user_id'])): ?>

                <li>
                    <a href="profile.php">Личный кабинет</a>
                </li>

                <?php if ($is_admin): ?>

                    <li>
                        <a href="admin_panel/admin.php">Админ-панель</a>
                    </li>

                <?php endif; ?>

                <li>
                    <a href="logout.php">Выход</a>
                </li>

            <?php else: ?>

                <li>
                    <a href="login.php">Вход</a>
                </li>

                <li>
                    <a href="register.php">Регистрация</a>
                </li>

            <?php endif; ?>

        </ul>
    </nav>

</header>

<main class="main-content">

<?php foreach ($sections as $sectionTitle => $category): ?>

    <section class="banner">

        <div class="banner-text">
            <h2><?php echo htmlspecialchars($sectionTitle); ?></h2>
        </div>

    </section>

    <section class="books-grid">

        <?php
        $stmt = $pdo->prepare("
            SELECT *
            FROM books
            WHERE category = :category
        ");

        $stmt->execute([
            ':category' => $category
        ]);

        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($books as $row):
        ?>

            <div class="book-card" id="book-<?php echo htmlspecialchars($row['id']); ?>">

                <div class="book-image">

                    <img
                        src="<?php echo htmlspecialchars($row['image']); ?>"
                        alt="<?php echo htmlspecialchars($row['title']); ?>"
                    >

                </div>

                <div class="book-info">

                    <h3 class="book-title">
                        <?php echo htmlspecialchars($row['title']); ?>
                    </h3>

                    <p class="book-author">
                        <?php echo htmlspecialchars($row['author']); ?>
                    </p>

                    <div class="book-price">
                        <?php echo htmlspecialchars($row['price']); ?> ₽
                    </div>

                    <?php if (isset($_SESSION['user_id'])): ?>

                        <form action="add_to_favorites.php#book-<?php echo htmlspecialchars($row['id']); ?>" method="POST">

                            <input
                                type="hidden"
                                name="book_id"
                                value="<?php echo htmlspecialchars($row['id']); ?>"
                            >

                            <input
                                type="hidden"
                                name="redirect_anchor"
                                value="book-<?php echo htmlspecialchars($row['id']); ?>"
                            >

                            <button type="submit" class="favorite-btn">
                                В избранное
                            </button>

                        </form>

                        <form action="add_to_cart.php#book-<?php echo htmlspecialchars($row['id']); ?>" method="POST">

                            <input
                                type="hidden"
                                name="book_id"
                                value="<?php echo htmlspecialchars($row['id']); ?>"
                            >

                            <input
                                type="hidden"
                                name="redirect_anchor"
                                value="book-<?php echo htmlspecialchars($row['id']); ?>"
                            >

                            <button type="submit" class="add-to-cart">
                                В корзину
                            </button>

                        </form>

                    <?php else: ?>

                        <a href="login.php" class="login-needed">
                            Войдите, чтобы купить
                        </a>

                    <?php endif; ?>

                </div>

            </div>

        <?php endforeach; ?>

    </section>

<?php endforeach; ?>

</main>

<footer class="footer">

    <div class="footer-content">

        <div class="footer-section about-section">

            <h3>О нас</h3>

            <p>
                Книжный магазин с 2010 года
            </p>

            <p class="copyright">
                Данный сайт создан как студенческая работа.
            </p>

        </div>

        <div class="footer-section contacts-section">

            <h3>Контакты</h3>

            <p>
                Горячая линия: 8-800-123-45-67
            </p>

            <p>
                Email: KnizhnyMir@mail.ru
            </p>

        </div>

    </div>

</footer>

</body>
</html>