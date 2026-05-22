<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "bookstore");

if (!$conn) {
    die("Ошибка подключения: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$user_check = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$current_user = mysqli_fetch_assoc($user_check);

if (!$current_user || $current_user['role'] !== 'admin') {
    die("Доступ запрещён. Эта страница только для администратора.");
}

if (isset($_POST['add_book'])) {

    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $author = mysqli_real_escape_string($conn, $_POST['author']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);

    $image = "";

    if (!empty($_FILES['image_file']['name'])) {

        $upload_dir = "../images/";

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $image_name = time() . "_" . basename($_FILES['image_file']['name']);
        $full_path = $upload_dir . $image_name;
        $db_path = "images/" . $image_name;

        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $full_path)) {
            $image = $db_path;
        }
    }

    mysqli_query($conn, "
        INSERT INTO books(title, author, price, category, image)
        VALUES('$title', '$author', '$price', '$category', '$image')
    ");

    header("Location: admin.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    mysqli_query($conn, "DELETE FROM favorites WHERE book_id='$id'");
    mysqli_query($conn, "DELETE FROM cart WHERE book_id='$id'");
    mysqli_query($conn, "DELETE FROM books WHERE id='$id'");

    header("Location: admin.php");
    exit();
}

if (isset($_POST['edit_book'])) {

    $id = intval($_POST['id']);

    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $author = mysqli_real_escape_string($conn, $_POST['author']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);

    $update_image = "";

    if (!empty($_FILES['image_file']['name'])) {

        $upload_dir = "../images/";

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $image_name = time() . "_" . basename($_FILES['image_file']['name']);
        $full_path = $upload_dir . $image_name;
        $db_path = "images/" . $image_name;

        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $full_path)) {
            $update_image = ", image='$db_path'";
        }
    }

    mysqli_query($conn, "
        UPDATE books 
        SET title='$title',
            author='$author',
            price='$price',
            category='$category'
            $update_image
        WHERE id='$id'
    ");

    header("Location: admin.php");
    exit();
}

$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS count FROM orders
"))['count'] ?? 0;

$total_users = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS count FROM users WHERE role='user'
"))['count'] ?? 0;

$total_sales = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(total_price) AS total FROM orders
"))['total'] ?? 0;

$total_books_sold = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(quantity) AS total FROM order_items
"))['total'] ?? 0;

$popular_books = mysqli_query($conn, "
    SELECT title, SUM(quantity) AS sold
    FROM order_items
    GROUP BY title
    ORDER BY sold DESC
    LIMIT 5
");

$sold_books = mysqli_query($conn, "
    SELECT 
        title,
        SUM(quantity) AS total_quantity
    FROM order_items
    GROUP BY title
    ORDER BY total_quantity DESC
");

$books = mysqli_query($conn, "SELECT * FROM books ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
    <link rel="stylesheet" href="../stylesite.css?v=3005">

</head>

<body>

<header class="header">
    <div class="logo">
        <h1>Книжный мир</h1>
        <p>Админ-панель</p>
    </div>

    <nav class="main-nav">
        <ul>
            <li><a href="../index.php">Главная</a></li>
            <li><a href="../profile.php">Личный кабинет</a></li>
            <li><a href="../logout.php">Выход</a></li>
        </ul>
    </nav>
</header>

<main class="admin-page">

    <section class="admin-stats">

        <div class="stat-card">
            <h3>Заказы</h3>
            <p><?php echo $total_orders ?: 0; ?></p>
        </div>

        <div class="stat-card">
            <h3>Пользователи</h3>
            <p><?php echo $total_users ?: 0; ?></p>
        </div>

        <div class="stat-card">
            <h3>Продажи</h3>
            <p><?php echo $total_sales ?: 0; ?> ₽</p>
        </div>

        <div class="stat-card">
            <h3>Продано книг</h3>
            <p><?php echo $total_books_sold ?: 0; ?></p>
        </div>

    </section>

    <section class="admin-box">
        <h2>Топ продаж книг</h2>

        <?php if ($popular_books && mysqli_num_rows($popular_books) > 0): ?>

            <table class="admin-table stats-table">
                <tr>
                    <th>Книга</th>
                    <th>Продано</th>
                </tr>

                <?php while($book_stat = mysqli_fetch_assoc($popular_books)): ?>
                    <tr>
                        <td><?php echo $book_stat['title']; ?></td>
                        <td><?php echo $book_stat['sold']; ?> шт.</td>
                    </tr>
                <?php endwhile; ?>
            </table>

        <?php else: ?>

            <p>Пока нет продаж.</p>

        <?php endif; ?>

    </section>

    <section class="admin-box">
        <h2>Все проданные книги</h2>

        <?php if ($sold_books && mysqli_num_rows($sold_books) > 0): ?>

            <table class="admin-table stats-table">
                <tr>
                    <th>Книга</th>
                    <th>Всего продано</th>
                </tr>

                <?php while($sold = mysqli_fetch_assoc($sold_books)): ?>

                    <tr>
                        <td><?php echo $sold['title']; ?></td>
                        <td><?php echo $sold['total_quantity']; ?> шт.</td>
                    </tr>

                <?php endwhile; ?>

            </table>

        <?php else: ?>

            <p>Проданных книг пока нет.</p>

        <?php endif; ?>

    </section>

    <section class="admin-box">
        <h2>Добавить книгу</h2>

        <form method="POST" enctype="multipart/form-data" class="admin-form">
            <input type="text" name="title" placeholder="Название" required>
            <input type="text" name="author" placeholder="Автор" required>
            <input type="number" name="price" placeholder="Цена" required>

            <select name="category" required>
                <option value="popular">Популярное</option>
                <option value="new">Новинки</option>
                <option value="exclusive">Эксклюзивно</option>
            </select>

            <input type="file" name="image_file" accept="image/*" required>

            <button type="submit" name="add_book">Добавить</button>
        </form>
    </section>

    <section class="admin-box">
        <h2>Управление книгами</h2>

        <table class="admin-table">
            <tr>
                <th>Обложка</th>
                <th>Книга</th>
                <th>Действия</th>
            </tr>

            <?php while($book = mysqli_fetch_assoc($books)): ?>

                <tr>
                    <td>
                        <img src="../<?php echo $book['image']; ?>" alt="">
                    </td>

                    <td>
                        <form method="POST" enctype="multipart/form-data" class="edit-form">
                            <input type="hidden" name="id" value="<?php echo $book['id']; ?>">

                            <input type="text" name="title" value="<?php echo $book['title']; ?>" required>
                            <input type="text" name="author" value="<?php echo $book['author']; ?>" required>
                            <input type="number" name="price" value="<?php echo $book['price']; ?>" required>

                            <select name="category" required>
                                <option value="popular" <?php if($book['category'] == 'popular') echo 'selected'; ?>>Популярное</option>
                                <option value="new" <?php if($book['category'] == 'new') echo 'selected'; ?>>Новинки</option>
                                <option value="exclusive" <?php if($book['category'] == 'exclusive') echo 'selected'; ?>>Эксклюзивно</option>
                            </select>

                            <input type="file" name="image_file" accept="image/*">

                            <button type="submit" name="edit_book" class="save-btn">
                                Сохранить
                            </button>

                            <a href="admin.php?delete=<?php echo $book['id']; ?>" 
                               class="delete-btn"
                               onclick="return confirm('Удалить книгу?');">
                                Удалить
                            </a>
                        </form>
                    </td>

                    <td></td>
                </tr>

            <?php endwhile; ?>

        </table>
    </section>

</main>

</body>
</html>