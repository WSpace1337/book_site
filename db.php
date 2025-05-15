<?php
$host = 'localhost';
$dbname = 'book_catalog';
$user = 'root';
$pass = ''; // змінити на свій пароль, якщо є

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Помилка підключення до БД: " . $e->getMessage());
}
?>
