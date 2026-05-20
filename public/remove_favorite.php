<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "bookstore");

if (!$conn) {
    die("Ошибка подключения");
}

mysqli_set_charset($conn, "utf8");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$book_id = $_POST['book_id'];

mysqli_query($conn, "
    DELETE FROM favorites
    WHERE user_id='$user_id'
    AND book_id='$book_id'
");

header("Location: favorites.php");
exit();
?>