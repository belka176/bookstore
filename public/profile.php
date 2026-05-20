<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "bookstore");

if (!$conn) {
    die("Ошибка подключения к БД: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    $update_avatar = "";

    if (!empty($_FILES['avatar']['name'])) {

        $upload_dir = __DIR__ . "/images/avatars/";

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $avatar_name = time() . "_" . basename($_FILES['avatar']['name']);
        $full_path = $upload_dir . $avatar_name;
        $db_path = "images/avatars/" . $avatar_name;

        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $full_path)) {
            $update_avatar = ", avatar='$db_path'";
        }
    }

    $update = "
        UPDATE users 
        SET 
            name='$name',
            email='$email',
            phone='$phone'
            $update_avatar
        WHERE id='$user_id'
    ";

    mysqli_query($conn, $update);

    header("Location: profile.php");
    exit();
}

$user_query = "SELECT * FROM users WHERE id='$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

$orders_query = mysqli_query($conn, "
    SELECT *
    FROM orders
    WHERE user_id = '$user_id'
    ORDER BY order_date ASC
");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет</title>

    <link rel="stylesheet" href="stylesite.css?v=2006">

    <style>
    .profile-page {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .profile-top {
        display: flex;
        gap: 40px;
        background: #19573a;
        padding: 40px;
        border-radius: 18px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        margin-bottom: 35px;
    }

    .avatar-section {
        width: 260px;
        flex-shrink: 0;
    }

    .avatar-box {
        width: 220px;
        height: 220px;
        border-radius: 50%;
        overflow: hidden;
        border: 6px solid #2a7d5c;
        background: white;
        box-shadow: 0 0 15px rgba(0,0,0,0.3);
    }

    .avatar-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .default-avatar {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 90px;
        background: #f0f6ee;
    }

    .profile-info {
        flex: 1;
    }

    .profile-info h2 {
        color: white;
        margin-top: 0;
        margin-bottom: 30px;
        font-size: 38px;
    }

    .profile-form {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 22px;
    }

    .profile-form-group {
        display: flex;
        flex-direction: column;
    }

    .profile-form-group label {
        color: #d4e6d4;
        margin-bottom: 8px;
        font-weight: bold;
        font-size: 15px;
    }

    .profile-form-group input {
        padding: 14px;
        border: none;
        border-radius: 8px;
        font-size: 15px;
    }

    .profile-save {
        grid-column: span 2;
    }

    .profile-save button {
        width: 100%;
        padding: 15px;
        border: none;
        border-radius: 8px;
        background: #2a7d5c;
        color: white;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: 0.3s;
    }

    .profile-save button:hover {
        background: #3a9d7c;
    }

    .profile-right {
        background: #19573a;
        padding: 35px;
        border-radius: 18px;
        color: white;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .profile-right h2 {
        margin-top: 0;
        margin-bottom: 25px;
        font-size: 32px;
    }

    .order-item {
        background: #f0f6ee;
        color: #19573a;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 20px;
    }

    .order-item h3 {
        margin-top: 0;
    }

    .order-item strong {
        color: #19573a;
    }

    .order-books {
        margin-top: 15px;
        padding-top: 12px;
        border-top: 2px solid #d4e6d4;
    }

    .order-book-row {
        margin-top: 8px;
        font-weight: bold;
    }

    .order-details-btn {
        display: inline-block;
        margin-top: 12px;
        background: #2a7d5c;
        color: white;
        padding: 12px 18px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: bold;
    }

    .order-details-btn:hover {
        background: #3a9d7c;
    }

    .empty-orders {
        background: #f0f6ee;
        color: #19573a;
        padding: 25px;
        border-radius: 12px;
        font-weight: bold;
    }

    @media (max-width: 900px) {
        .profile-top {
            flex-direction: column;
            align-items: center;
        }

        .profile-form {
            grid-template-columns: 1fr;
        }

        .profile-save {
            grid-column: span 1;
        }

        .avatar-section {
            width: 100%;
            display: flex;
            justify-content: center;
        }
    }
    </style>
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

<main class="profile-page">

    <div class="profile-top">

        <div class="avatar-section">

            <div class="avatar-box">

                <?php if (!empty($user['avatar'])): ?>

                    <img src="<?php echo $user['avatar'] . '?v=' . time(); ?>" alt="Аватар">

                <?php else: ?>

                    <div class="default-avatar">👤</div>

                <?php endif; ?>

            </div>

        </div>

        <div class="profile-info">

            <h2>Личный кабинет</h2>

            <form method="POST" enctype="multipart/form-data" class="profile-form">

                <div class="profile-form-group">
                    <label>Загрузить аватар</label>
                    <input type="file" name="avatar" accept="image/*">
                </div>

                <div class="profile-form-group">
                    <label>Имя</label>
                    <input type="text" name="name" value="<?php echo $user['name'] ?? ''; ?>">
                </div>

                <div class="profile-form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo $user['email'] ?? ''; ?>">
                </div>

                <div class="profile-form-group">
                    <label>Телефон</label>
                    <input type="text" name="phone" value="<?php echo $user['phone'] ?? ''; ?>">
                </div>

                <div class="profile-save">
                    <button type="submit">Сохранить изменения</button>
                </div>

            </form>

        </div>

    </div>

    <div class="profile-right">

        <h2>История покупок</h2>

        <?php if ($orders_query && mysqli_num_rows($orders_query) > 0): ?>

            <?php
            $order_number = 1;

            while($order = mysqli_fetch_assoc($orders_query)):
            ?>

                <div class="order-item">

                    <h3>Заказ №<?php echo $order_number; ?></h3>

                    <p>
                        <strong>Дата:</strong>

                        <?php
                            $date = new DateTime($order['order_date']);
                            $date->setTimezone(new DateTimeZone('Asia/Irkutsk'));

                            echo $date->format('Y-m-d H:i:s');
                        ?>
                    </p>

                    <p>
                        <strong>Сумма:</strong>
                        <?php echo $order['total_price']; ?> ₽
                    </p>

                    <p>
                        <strong>Статус:</strong>
                        <?php echo $order['status']; ?>
                    </p>

                    <?php
                    $order_id = $order['id'];

                    $books_query = mysqli_query($conn, "
                        SELECT *
                        FROM order_items
                        WHERE order_id = '$order_id'
                    ");
                    ?>

                    <div class="order-books">

                        <strong>Книги в заказе:</strong>

                        <?php if ($books_query && mysqli_num_rows($books_query) > 0): ?>

                            <?php while($book = mysqli_fetch_assoc($books_query)): ?>

                                <div class="order-book-row">
                                    📚 <?php echo $book['title']; ?> — <?php echo $book['quantity']; ?> шт.
                                </div>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <div class="order-book-row">
                                Нет данных о книгах.
                            </div>

                        <?php endif; ?>

                    </div>

                    <a href="order_details.php?id=<?php echo $order['id']; ?>" class="order-details-btn">
                        Подробнее
                    </a>

                </div>

            <?php
            $order_number++;
            endwhile;
            ?>

        <?php else: ?>

            <div class="empty-orders">
                У вас пока нет покупок.
            </div>

        <?php endif; ?>

    </div>

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