<?php
session_start();

require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$delivery_method = $_POST['delivery_method'] ?? '';
$address = $_POST['address'] ?? '';
$phone = $_POST['phone'] ?? '';
$comment = $_POST['comment'] ?? '';

if (isset($_POST['book_id'])) {

    $book_id = (int) $_POST['book_id'];

    $stmt = $pdo->prepare("
        SELECT books.id, books.title, books.author, books.price, cart.quantity
        FROM cart
        JOIN books ON cart.book_id = books.id
        WHERE cart.user_id = :user_id
        AND cart.book_id = :book_id
    ");

    $stmt->execute([
        ':user_id' => $user_id,
        ':book_id' => $book_id
    ]);

} else {

    $stmt = $pdo->prepare("
        SELECT books.id, books.title, books.author, books.price, cart.quantity
        FROM cart
        JOIN books ON cart.book_id = books.id
        WHERE cart.user_id = :user_id
    ");

    $stmt->execute([
        ':user_id' => $user_id
    ]);
}

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$items) {
    header("Location: cart.php");
    exit();
}

$total_price = 0;

foreach ($items as $item) {
    $total_price += $item['price'] * $item['quantity'];
}

$stmt = $pdo->prepare("
    INSERT INTO orders (user_id, total_price, status, order_date)
    VALUES (:user_id, :total_price, 'Оформлен', NOW())
");

$stmt->execute([
    ':user_id' => $user_id,
    ':total_price' => $total_price
]);

$order_id = $pdo->lastInsertId();

foreach ($items as $item) {

    $stmt = $pdo->prepare("
        INSERT INTO order_items (order_id, book_id, title, author, price, quantity)
        VALUES (:order_id, :book_id, :title, :author, :price, :quantity)
    ");

    $stmt->execute([
        ':order_id' => $order_id,
        ':book_id' => $item['id'],
        ':title' => $item['title'],
        ':author' => $item['author'],
        ':price' => $item['price'],
        ':quantity' => $item['quantity']
    ]);
}

if (isset($_POST['book_id'])) {

$book_id = (int) $_POST['book_id'];

    $stmt = $pdo->prepare("
        DELETE FROM cart
        WHERE user_id = :user_id
        AND book_id = :book_id
    ");

    $stmt->execute([
        ':user_id' => $user_id,
        ':book_id' => $book_id
    ]);

} else {

    $stmt = $pdo->prepare("
        DELETE FROM cart
        WHERE user_id = :user_id
    ");

    $stmt->execute([
        ':user_id' => $user_id
    ]);
}

header("Location: profile.php");
exit();
?>