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
    SELECT id
    FROM favorites
    WHERE user_id = :user_id
    AND book_id = :book_id
");

$stmt->execute([
    ':user_id' => $user_id,
    ':book_id' => $book_id
]);

$favorite = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$favorite) {

    $stmt = $pdo->prepare("
        INSERT INTO favorites (user_id, book_id)
        VALUES (:user_id, :book_id)
    ");

    $stmt->execute([
        ':user_id' => $user_id,
        ':book_id' => $book_id
    ]);
}

$anchor = $_POST['redirect_anchor'] ?? '';

if (!empty($anchor)) {
    header("Location: index.php#$anchor");
} else {
    header("Location: index.php");
}

exit();
?>