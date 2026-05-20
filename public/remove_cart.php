<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "bookstore");
mysqli_set_charset($conn, "utf8");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$cart_id = $_POST['cart_id'];

mysqli_query($conn, "
    DELETE FROM cart 
    WHERE id='$cart_id'
");

header("Location: cart.php");
exit();
?>