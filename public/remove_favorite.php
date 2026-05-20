<?php
session_start();

require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$book_id = $_POST['book_id'];

$stmt = $pdo->prepare("
    DELETE FROM favorites
    WHERE user_id = :user_id
    AND book_id = :book_id
");

$stmt->execute([
    ':user_id' => $user_id,
    ':book_id' => $book_id
]);

header("Location: favorites.php");
exit();
?>