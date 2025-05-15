<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Неавторизованный пользователь']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$book_id = $data['book_id'] ?? null;

if (!$book_id) {
    echo json_encode(['success' => false, 'message' => 'Неверные данные']);
    exit();
}

// Проверяем, лайкнул ли уже
$stmt = $pdo->prepare("SELECT id FROM user_likes WHERE user_id = ? AND book_id = ?");
$stmt->execute([$user_id, $book_id]);
$like = $stmt->fetch();

if ($like) {
    // Удаляем лайк
    $stmt = $pdo->prepare("DELETE FROM user_likes WHERE id = ?");
    $stmt->execute([$like['id']]);
    echo json_encode(['success' => true, 'liked' => false]);
} else {
    // Добавляем лайк
    $stmt = $pdo->prepare("INSERT INTO user_likes (user_id, book_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $book_id]);
    echo json_encode(['success' => true, 'liked' => true]);
}
