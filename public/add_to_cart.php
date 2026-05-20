<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "bookstore");

if (!$conn) {
    die("Ошибка подключения к базе данных");
}

mysqli_set_charset($conn, "utf8");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$book_id = $_POST['book_id'];

$check = mysqli_query($conn, "
    SELECT * FROM cart 
    WHERE user_id='$user_id' AND book_id='$book_id'
");

if (mysqli_num_rows($check) > 0) {

    mysqli_query($conn, "
        UPDATE cart 
        SET quantity = quantity + 1
        WHERE user_id='$user_id' AND book_id='$book_id'
    ");

} else {

    mysqli_query($conn, "
        INSERT INTO cart(user_id, book_id, quantity)
        VALUES('$user_id', '$book_id', 1)
    ");
}

$anchor = $_POST['redirect_anchor'] ?? '';

if (!empty($anchor)) {
    header("Location: index.php#$anchor");
} else {
    header("Location: index.php");
}

exit();
?>