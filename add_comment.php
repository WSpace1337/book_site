<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $book_id = $_POST['book_id'] ?? null;
    $comment = trim($_POST['comment'] ?? '');

    if ($book_id && $comment !== '') {
        $stmt = $pdo->prepare("INSERT INTO comments (user_id, book_id, comment) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $book_id, $comment]);
    }
    header("Location: book.php?id=" . urlencode($book_id));
    exit();
}
