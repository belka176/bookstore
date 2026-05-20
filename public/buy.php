<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "bookstore");

if (!$conn) {
    die("Ошибка подключения к базе данных: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$delivery_method = mysqli_real_escape_string($conn, $_POST['delivery_method'] ?? '');
$address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');
$phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
$comment = mysqli_real_escape_string($conn, $_POST['comment'] ?? '');

if (isset($_POST['book_id'])) {

    $book_id = intval($_POST['book_id']);

    $result = mysqli_query($conn, "
        SELECT books.id, books.title, books.author, books.price, cart.quantity
        FROM cart
        JOIN books ON cart.book_id = books.id
        WHERE cart.user_id = '$user_id'
        AND cart.book_id = '$book_id'
    ");

} else {

    $result = mysqli_query($conn, "
        SELECT books.id, books.title, books.author, books.price, cart.quantity
        FROM cart
        JOIN books ON cart.book_id = books.id
        WHERE cart.user_id = '$user_id'
    ");
}

if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: cart.php");
    exit();
}

$items = [];
$total_price = 0;

while ($item = mysqli_fetch_assoc($result)) {
    $items[] = $item;
    $total_price += $item['price'] * $item['quantity'];
}

mysqli_query($conn, "
    INSERT INTO orders(user_id, total_price, status, order_date)
    VALUES('$user_id', '$total_price', 'Оформлен', NOW())
");

$order_id = mysqli_insert_id($conn);

foreach ($items as $item) {
    $book_id = $item['id'];
    $title = mysqli_real_escape_string($conn, $item['title']);
    $author = mysqli_real_escape_string($conn, $item['author']);
    $price = $item['price'];
    $quantity = $item['quantity'];

    mysqli_query($conn, "
        INSERT INTO order_items(order_id, book_id, title, author, price, quantity)
        VALUES('$order_id', '$book_id', '$title', '$author', '$price', '$quantity')
    ");
}

if (isset($_POST['book_id'])) {
    mysqli_query($conn, "
        DELETE FROM cart
        WHERE user_id = '$user_id'
        AND book_id = '$book_id'
    ");
} else {
    mysqli_query($conn, "
        DELETE FROM cart
        WHERE user_id = '$user_id'
    ");
}

header("Location: profile.php");
exit();
?>