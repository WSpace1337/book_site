<?php
session_start();
require 'db.php';
$genres = [
    "fantasy" => "Фантастика",
    "detective" => "Детектив",
    "romance" => "Роман"
];
// ... код авторизации и обработки формы ...

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $genre = $_POST['genre'] ?? '';
    $description = $_POST['description'] ?? '';
    $photo = $_FILES['photo'] ?? null;
    $file = $_FILES['file'] ?? null;  // файл книги

    $errors = [];

    // Проверки
    if (!$title) $errors[] = 'Введите название книги';
    if (!$genre) $errors[] = 'Выберите жанр';
    if (!$description) $errors[] = 'Введите описание';
    if (!$photo || $photo['error'] !== UPLOAD_ERR_OK) $errors[] = 'Загрузите фото книги';
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) $errors[] = 'Загрузите файл книги (PDF)';

    // Проверим расширения и типы
    $allowed_photo_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($photo['type'], $allowed_photo_types)) {
        $errors[] = 'Фото должно быть JPEG, PNG или GIF';
    }

    if ($file['type'] !== 'application/pdf') {
        $errors[] = 'Файл книги должен быть PDF';
    }

    if (empty($errors)) {
        // Папки для загрузки (создай их на сервере, если нет)
        $uploadDirPhotos = __DIR__ . '/uploads/photos/';
        $uploadDirFiles = __DIR__ . '/uploads/files/';

        if (!is_dir($uploadDirPhotos)) mkdir($uploadDirPhotos, 0777, true);
        if (!is_dir($uploadDirFiles)) mkdir($uploadDirFiles, 0777, true);

        // Уникальные имена файлов
        $photoName = uniqid() . '_' . basename($photo['name']);
        $fileName = uniqid() . '_' . basename($file['name']);

        $photoPath = $uploadDirPhotos . $photoName;
        $filePath = $uploadDirFiles . $fileName;

        // Перемещаем загруженные файлы
        if (move_uploaded_file($photo['tmp_name'], $photoPath) && move_uploaded_file($file['tmp_name'], $filePath)) {
            // Сохраняем в БД пути относительно корня сайта (чтобы потом вывести)
            $photoDbPath = 'uploads/photos/' . $photoName;
            $fileDbPath = 'uploads/files/' . $fileName;

            $stmt = $pdo->prepare("INSERT INTO books (title, genre, description, photo, file) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $genre, $description, $photoDbPath, $fileDbPath]);

            header("Location: index.php");
            exit();
        } else {
            $errors[] = 'Ошибка при сохранении файлов';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Додати книгу</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
<header class="bg-blue-700 text-white p-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold">Додати книгу</h1>
    <a href="index.php" class="bg-gray-300 hover:bg-gray-400 text-black px-4 py-2 rounded text-sm">Назад до каталогу</a>
</header>

<main class="p-6">
    <form method="POST" enctype="multipart/form-data" class="grid gap-4 max-w-xl mx-auto bg-white p-6 rounded shadow">
        <input name="title" type="text" placeholder="Назва книги" class="p-2 border rounded" required>
        <input name="author" type="text" placeholder="Автор" class="p-2 border rounded" required>
        
        <select name="genre" class="p-2 border rounded" required>
            <option value="">Оберіть жанр</option>
            <?php foreach ($genres as $key => $label): ?>
                <option value="<?= $key ?>"><?= $label ?></option>
            <?php endforeach; ?>
        </select>

        <textarea name="description" placeholder="Короткий опис книги" class="p-2 border rounded h-24"></textarea>
        
        <div class="mb-4">
        <label for="photo" class="block font-semibold mb-1">Обкладинка книги (фото):</label>
        <input type="file" name="photo" id="photo" accept="image/*" class="p-2 border rounded w-full">
        <img id="photo-preview" src="#" alt="Прев’ю обкладинки" class="mt-4 max-h-64 rounded hidden">
        </div>

        <label for="file" class="block font-semibold mb-1">Файл книги (PDF):</label>
        <input type="file" name="file" id="file" accept=".pdf,.epub,.doc,.docx" class="mb-4 p-2 border rounded w-full">


        <div id="file-preview" class="flex items-center space-x-2 text-gray-700 text-sm hidden">
        <span id="file-icon" class="text-xl">📄</span>
        <span id="file-name"></span>
        </div>

        <button type="submit" class="bg-green-600 text-white p-2 rounded hover:bg-green-700">
            Додати книгу
        </button>
    </form>
</main>
        <script>
        const fileInput = document.getElementById('file');
        const filePreview = document.getElementById('file-preview');
        const fileName = document.getElementById('file-name');
        const fileIcon = document.getElementById('file-icon');

        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) {
            filePreview.classList.add('hidden');
            return;
            }

            // Визначення іконки по розширенню файлу
            const ext = file.name.split('.').pop().toLowerCase();
            let icon = '📄'; // за замовчуванням

            if (ext === 'pdf') icon = '📕';
            else if (ext === 'epub') icon = '📚';
            else if (ext === 'doc' || ext === 'docx') icon = '📝';

            fileIcon.textContent = icon;
            fileName.textContent = file.name;
            filePreview.classList.remove('hidden');
        });
        </script>
</body>
</html>
