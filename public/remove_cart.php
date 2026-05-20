<?php
session_start();

require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$cart_id = $_POST['cart_id'];

$stmt = $pdo->prepare("
    DELETE FROM cart
    WHERE id = :cart_id
    AND user_id = :user_id
");

$stmt->execute([
    ':cart_id' => $cart_id,
    ':user_id' => $user_id
]);

header("Location: cart.php");
exit();
?>