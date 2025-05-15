<?php
session_start();
require 'db.php';

$book_id = $_GET['id'] ?? null;
if (!$book_id) {
    header("Location: index.php");
    exit();
}

// Получаем данные книги
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$book) {
    echo "Книга не найдена";
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;

// Если пользователь залогинен — записываем посещение
if ($user_id) {
    // Проверим, есть ли уже запись сегодня (чтобы не плодить записи)
    $stmt = $pdo->prepare("SELECT id FROM user_visits WHERE user_id = ? AND book_id = ? AND DATE(visited_at) = CURDATE()");
    $stmt->execute([$user_id, $book_id]);
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO user_visits (user_id, book_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $book_id]);
    }

    // Проверим, лайкнул ли пользователь эту книгу
    $stmt = $pdo->prepare("SELECT id FROM user_likes WHERE user_id = ? AND book_id = ?");
    $stmt->execute([$user_id, $book_id]);
    $liked = (bool)$stmt->fetch();
} else {
    $liked = false;
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title><?=htmlspecialchars($book['title'])?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        async function toggleLike(bookId) {
            const response = await fetch('toggle_like.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({book_id: bookId})
            });
            const data = await response.json();
            if (data.success) {
                const btn = document.getElementById('like-btn');
                btn.textContent = data.liked ? '❤️ Понравилась' : '🤍 Понравилась';
            } else {
                alert('Ошибка: ' + data.message);
            }
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen p-6 flex flex-col items-center">
    <div class="w-full max-w-5xl mb-6">
  <a href="index.php" class="text-blue-600 hover:underline flex items-center font-semibold">
    ← На главную
  </a>
    </div>

    <div class="bg-white max-w-3xl rounded shadow p-6 w-full">
        <h1 class="text-3xl font-bold mb-4"><?=htmlspecialchars($book['title'])?></h1>
       <img src="<?=htmlspecialchars($book['photo'])?>" alt="<?=htmlspecialchars($book['title'])?>" class="w-full h-auto max-h-96 object-contain rounded mb-4" />
        <p class="mb-4 whitespace-pre-line"><?=htmlspecialchars($book['description'])?></p>

        <?php if ($user_id): ?>
            <button id="like-btn"
                onclick="toggleLike(<?= (int)$book_id ?>)"
                class="px-4 py-2 rounded text-white font-semibold <?= $liked ? 'bg-red-600 hover:bg-red-700' : 'bg-gray-400 hover:bg-gray-500' ?>"
            >
                <?= $liked ? '❤️ Понравилась' : '🤍 Понравилась' ?>
            </button>
        <?php else: ?>
            <p class="text-gray-600 italic">Войдите, чтобы поставить лайк.</p>
        <?php endif; ?>

        <div class="mt-6">
            <a href="<?=htmlspecialchars($book['file'])?>" download class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded font-semibold">Скачать книгу</a>
        </div>
    </div>
</body>
</html>
<?php
// Получаем комментарии к книге
$stmt = $pdo->prepare("SELECT c.comment, c.created_at, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.book_id = ? ORDER BY c.created_at DESC");
$stmt->execute([$book_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<<div class="mt-10 max-w-3xl bg-white p-6 rounded-lg shadow-lg">
    <h2 class="text-3xl font-bold mb-6 border-b pb-2">Комментарии</h2>

    <?php if ($user_id): ?>
        <form method="post" class="mb-8" action="add_comment.php">
            <input type="hidden" name="book_id" value="<?= (int)$book_id ?>">
            <textarea 
                name="comment" 
                rows="4" 
                required 
                class="w-full border border-gray-300 rounded-lg p-4 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none mb-4" 
                placeholder="Оставьте комментарий..."
            ></textarea>
            <button 
                type="submit" 
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-colors duration-300"
            >
                Отправить
            </button>
        </form>
    <?php else: ?>
        <p class="text-gray-600 italic mb-6">Войдите, чтобы оставить комментарий.</p>
    <?php endif; ?>

    <?php if (count($comments) === 0): ?>
        <p class="text-gray-500 italic">Пока нет комментариев.</p>
    <?php else: ?>
        <div class="space-y-6">
        <?php foreach ($comments as $c): ?>
            <div class="bg-gray-50 rounded-lg shadow-sm p-5 border border-gray-200">
                <p class="text-gray-800 text-base leading-relaxed"><?= nl2br(htmlspecialchars($c['comment'])) ?></p>
                <div class="mt-4 flex justify-between text-sm text-gray-500 italic">
                    <span>— <?= htmlspecialchars($c['username']) ?></span>
                    <span><?= date('d.m.Y H:i', strtotime($c['created_at'])) ?></span>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

