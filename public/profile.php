<?php
session_start();

require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';

    $avatar_sql = "";

    $params = [
        ':name' => $name,
        ':email' => $email,
        ':phone' => $phone,
        ':user_id' => $user_id
    ];

    if (!empty($_FILES['avatar']['name'])) {

        $upload_dir = __DIR__ . "/images/avatars/";

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $avatar_name = time() . "_" . basename($_FILES['avatar']['name']);
        $full_path = $upload_dir . $avatar_name;
        $db_path = "images/avatars/" . $avatar_name;

        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $full_path)) {
            $avatar_sql = ", avatar = :avatar";
            $params[':avatar'] = $db_path;
        }
    }

    $stmt = $pdo->prepare("
        UPDATE users
        SET
            name = :name,
            email = :email,
            phone = :phone
            $avatar_sql
        WHERE id = :user_id
    ");

    $stmt->execute($params);

    header("Location: profile.php");
    exit();
}

$stmt = $pdo->prepare("
    SELECT *
    FROM users
    WHERE id = :user_id
");

$stmt->execute([
    ':user_id' => $user_id
]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT *
    FROM orders
    WHERE user_id = :user_id
    ORDER BY order_date ASC
");

$stmt->execute([
    ':user_id' => $user_id
]);

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет</title>

    <link rel="stylesheet" href="stylesite.css?v=2006">
    
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

                    <img
                        src="<?php echo htmlspecialchars($user['avatar']) . '?v=' . time(); ?>"
                        alt="Аватар"
                    >

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
                    <input
                        type="text"
                        name="name"
                        value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>"
                    >
                </div>

                <div class="profile-form-group">
                    <label>Email</label>
                    <input
                        type="email"
                        name="email"
                        value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                    >
                </div>

                <div class="profile-form-group">
                    <label>Телефон</label>
                    <input
                        type="text"
                        name="phone"
                        value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                    >
                </div>

                <div class="profile-save">
                    <button type="submit">Сохранить изменения</button>
                </div>

            </form>

        </div>

    </div>

    <div class="profile-right">

        <h2>История покупок</h2>

        <?php if (count($orders) > 0): ?>

            <?php
            $order_number = 1;

            foreach ($orders as $order):
            ?>

                <div class="order-item">

                    <h3>Заказ №<?php echo $order_number; ?></h3>

                    <p>
                        <strong>Дата:</strong>

                        <?php
                            $date = new DateTime($order['order_date']);
                            $date->setTimezone(new DateTimeZone('Asia/Irkutsk'));

                            echo htmlspecialchars($date->format('Y-m-d H:i:s'));
                        ?>
                    </p>

                    <p>
                        <strong>Сумма:</strong>
                        <?php echo htmlspecialchars($order['total_price']); ?> ₽
                    </p>

                    <p>
                        <strong>Статус:</strong>
                        <?php echo htmlspecialchars($order['status']); ?>
                    </p>

                    <?php
                    $stmt = $pdo->prepare("
                        SELECT *
                        FROM order_items
                        WHERE order_id = :order_id
                    ");

                    $stmt->execute([
                        ':order_id' => $order['id']
                    ]);

                    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <div class="order-books">

                        <strong>Книги в заказе:</strong>

                        <?php if (count($books) > 0): ?>

                            <?php foreach ($books as $book): ?>

                                <div class="order-book-row">
                                    📚 <?php echo htmlspecialchars($book['title']); ?> —
                                    <?php echo htmlspecialchars($book['quantity']); ?> шт.
                                </div>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <div class="order-book-row">
                                Нет данных о книгах.
                            </div>

                        <?php endif; ?>

                    </div>

                    <a
                        href="order_details.php?id=<?php echo htmlspecialchars($order['id']); ?>"
                        class="order-details-btn"
                    >
                        Подробнее
                    </a>

                </div>

            <?php
            $order_number++;
            endforeach;
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