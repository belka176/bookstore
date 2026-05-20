<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "bookstore");

if (!$conn) {
    die("Ошибка подключения к базе данных: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

$is_admin = false;

if (isset($_SESSION['user_id'])) {

    $user_id = $_SESSION['user_id'];

    $admin_check = mysqli_query($conn, "
        SELECT role 
        FROM users 
        WHERE id = '$user_id'
    ");

    if ($admin_check && mysqli_num_rows($admin_check) > 0) {

        $admin_user = mysqli_fetch_assoc($admin_check);

        if ($admin_user['role'] == 'admin') {
            $is_admin = true;
        }
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

    <style>

.success-message {
    position: fixed;
    top: 30px;
    left: 50%;
    transform: translateX(-50%);
    background: #2a7d5c;
    color: white;
    padding: 18px 32px;
    border-radius: 14px;
    font-weight: bold;
    font-size: 17px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.25);
    z-index: 9999;

    animation: showMessage 0.45s ease;
}

@keyframes showMessage {

    from {
        opacity: 0;
        transform: translate(-50%, -25px);
    }

    to {
        opacity: 1;
        transform: translate(-50%, 0);
    }
}

</style>
</head>

<body>

<?php if(isset($_SESSION['success_login'])): ?>

    <div class="success-message">

        <?php
            echo $_SESSION['success_login'];
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

            <?php if(isset($_SESSION['user_id'])): ?>

                <li>
                    <a href="profile.php">
                        Личный кабинет
                    </a>
                </li>

                <?php if($is_admin): ?>

                    <li>
                        <a href="admin_panel/admin.php">
                            Админ-панель
                        </a>
                    </li>

                <?php endif; ?>

                <li>
                    <a href="logout.php">
                        Выход
                    </a>
                </li>

            <?php else: ?>

                <li>
                    <a href="login.php">
                        Вход
                    </a>
                </li>

                <li>
                    <a href="register.php">
                        Регистрация
                    </a>
                </li>

            <?php endif; ?>

        </ul>
    </nav>

</header>

<main class="main-content">

<?php foreach ($sections as $sectionTitle => $category): ?>

    <section class="banner">

        <div class="banner-text">
            <h2><?php echo $sectionTitle; ?></h2>
        </div>

    </section>

    <section class="books-grid">

        <?php
        $query = "SELECT * FROM books WHERE category = '$category'";
        $result = mysqli_query($conn, $query);

        while($row = mysqli_fetch_assoc($result)):
        ?>

            <div class="book-card" id="book-<?php echo $row['id']; ?>">

                <div class="book-image">

                    <img 
                        src="<?php echo $row['image']; ?>" 
                        alt="<?php echo $row['title']; ?>"
                    >

                </div>

                <div class="book-info">

                    <h3 class="book-title">
                        <?php echo $row['title']; ?>
                    </h3>

                    <p class="book-author">
                        <?php echo $row['author']; ?>
                    </p>

                    <div class="book-price">
                        <?php echo $row['price']; ?> ₽
                    </div>

                    <?php if(isset($_SESSION['user_id'])): ?>

                        <form action="add_to_favorites.php#book-<?php echo $row['id']; ?>" method="POST">

                            <input 
                                type="hidden" 
                                name="book_id" 
                                value="<?php echo $row['id']; ?>"
                            >

                            <input 
                                type="hidden" 
                                name="redirect_anchor" 
                                value="book-<?php echo $row['id']; ?>"
                            >

                            <button type="submit" class="favorite-btn">
                                В избранное
                            </button>

                        </form>

                        <form action="add_to_cart.php#book-<?php echo $row['id']; ?>" method="POST">

                            <input 
                                type="hidden" 
                                name="book_id" 
                                value="<?php echo $row['id']; ?>"
                            >

                            <input 
                                type="hidden" 
                                name="redirect_anchor" 
                                value="book-<?php echo $row['id']; ?>"
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

        <?php endwhile; ?>

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
                © 2025 Книжный мир. Все права защищены.
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