<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = '';

// Получаем данные пользователя
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Обработка смены пароля (код из предыдущего ответа)
// ... (копируй сюда код смены пароля из предыдущего варианта) ...

// Получаем последние посещённые книги (5 штук)
$stmt = $pdo->prepare("
    SELECT b.id, b.title, b.photo 
    FROM user_visits uv
    JOIN books b ON uv.book_id = b.id
    WHERE uv.user_id = ?
    ORDER BY uv.visited_at DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$visited_books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем понравившиеся книги (5 штук)
$stmt = $pdo->prepare("
    SELECT b.id, b.title, b.photo 
    FROM user_likes ul
    JOIN books b ON ul.book_id = b.id
    WHERE ul.user_id = ?
    ORDER BY ul.liked_at DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$liked_books = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Профиль пользователя</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center py-10">

<div class="w-full max-w-5xl mb-6">
  <a href="index.php" class="text-blue-600 hover:underline flex items-center font-semibold">
    ← На главную
  </a>
</div>

<div class="bg-white p-8 rounded shadow-md w-full max-w-5xl">
    <h1 class="text-3xl font-bold mb-6 text-center">Профиль пользователя</h1>

    <div class="flex flex-col md:flex-row md:space-x-10">
        <!-- Левая колонка: инфо и смена пароля -->
        <div class="md:w-1/3 mb-8 md:mb-0">
            <p class="mb-2"><strong>Имя пользователя:</strong> <?=htmlspecialchars($user['username'])?></p>
            <p class="mb-6"><strong>Email:</strong> <?=htmlspecialchars($user['email'])?></p>

            <h2 class="text-xl font-semibold mb-4">Сменить пароль</h2>

            <?php if ($errors): ?>
                <div class="mb-4 text-red-600">
                    <ul class="list-disc list-inside">
                        <?php foreach ($errors as $e): ?>
                            <li><?=htmlspecialchars($e)?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="mb-4 text-green-600"><?=htmlspecialchars($success)?></div>
            <?php endif; ?>

            <form method="post" class="space-y-4">
                <label class="block font-semibold">Текущий пароль</label>
                <input type="password" name="current_password" required class="w-full p-2 border rounded" />

                <label class="block font-semibold">Новый пароль</label>
                <input type="password" name="new_password" required class="w-full p-2 border rounded" />

                <label class="block font-semibold">Подтверждение нового пароля</label>
                <input type="password" name="new_password_confirm" required class="w-full p-2 border rounded" />

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded font-semibold">Обновить пароль</button>
            </form>

            <a href="logout.php" class="mt-6 block text-center text-red-600 hover:underline font-semibold">Выйти</a>
        </div>

        <!-- Правая колонка: посещённые и понравившиеся книги -->
        <div class="md:w-2/3 space-y-8">

            <div>
                <h2 class="text-2xl font-bold mb-4">Последние посещённые книги</h2>
                <?php if ($visited_books): ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                        <?php foreach ($visited_books as $book): ?>
                            <a href="book.php?id=<?= $book['id'] ?>" class="block border rounded overflow-hidden shadow hover:shadow-lg transition">
                                <img src="<?=htmlspecialchars($book['photo'])?>" alt="<?=htmlspecialchars($book['title'])?>" class="w-full h-48 object-cover">
                                <div class="p-2 font-semibold text-center"><?=htmlspecialchars($book['title'])?></div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-600 italic">Вы еще не посещали книги.</p>
                <?php endif; ?>
            </div>

            <div>
                <h2 class="text-2xl font-bold mb-4">Понравившиеся книги</h2>
                <?php if ($liked_books): ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                        <?php foreach ($liked_books as $book): ?>
                            <a href="book.php?id=<?= $book['id'] ?>" class="block border rounded overflow-hidden shadow hover:shadow-lg transition">
                                <img src="<?=htmlspecialchars($book['photo'])?>" alt="<?=htmlspecialchars($book['title'])?>" class="w-full h-48 object-cover">
                                <div class="p-2 font-semibold text-center"><?=htmlspecialchars($book['title'])?></div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-600 italic">Вы еще не ставили лайки книгам.</p>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

</body>
</html>
