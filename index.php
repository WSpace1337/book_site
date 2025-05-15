<?php
require 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$genres = [
    "fantasy" => "Фантастика",
    "detective" => "Детектив",
    "romance" => "Роман"
];

$selected_genre = $_GET['genre'] ?? 'all';

if ($selected_genre === 'all') {
    $stmt = $pdo->query("SELECT * FROM books ORDER BY created_at DESC");
} else {
    $stmt = $pdo->prepare("SELECT * FROM books WHERE genre = ? ORDER BY created_at DESC");
    $stmt->execute([$selected_genre]);
}
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Книжковий Каталог</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
    <header class="bg-blue-700 text-white p-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold">Книжковий Каталог</h1>
    <div class="flex items-center space-x-4">
        <span>Привіт, <?=htmlspecialchars($_SESSION['username'] ?? 'Гість')?>!</span>
        <a href="profile.php" class="underline hover:text-gray-300 text-sm">Профіль</a>
        <a href="add.php" class="bg-green-500 hover:bg-green-600 px-4 py-2 rounded text-white text-sm">Додати книгу</a>
        <a href="logout.php" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded text-white text-sm">Вийти</a>
    </div>
</header>

    <main class="p-6">
        <form method="get" class="mb-6">
            <label for="genre" class="block mb-2 font-semibold">Фільтрувати за жанром:</label>
            <select name="genre" id="genre" class="p-2 rounded border border-gray-300 w-64" onchange="this.form.submit()">
                <option value="all" <?= $selected_genre === 'all' ? 'selected' : '' ?>>Усі жанри</option>
                <?php foreach ($genres as $key => $label): ?>
                    <option value="<?= $key ?>" <?= $selected_genre === $key ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </form>

        <div class="grid md:grid-cols-3 gap-6">
            <?php foreach ($books as $book): ?>
                <a href="book.php?id=<?= $book['id'] ?>" class="block p-4 bg-white rounded shadow hover:shadow-md transition">

                    <?php if (!empty($book['photo'])): ?>
                        <img src="<?= htmlspecialchars($book['photo']) ?>" alt="Обкладинка" class="w-full aspect-[3/4] object-cover mb-4 rounded">
                    <?php endif; ?>

                    <h3 class="text-xl font-bold"><?= htmlspecialchars($book['title']) ?></h3>
                    <p class="text-sm text-gray-600 mb-1"><?= htmlspecialchars($book['author']) ?></p>
                    <p class="text-sm italic mb-2 text-gray-500">Жанр: <?= $genres[$book['genre']] ?? 'Невідомо' ?></p>

                    <?php if (!empty($book['description'])): ?>
                        <p class="text-sm text-gray-700"><?= nl2br(htmlspecialchars($book['description'])) ?></p>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>
