<?php
session_start();
require 'db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username) $errors[] = "Введіть ім'я користувача";
    if (!$password) $errors[] = "Введіть пароль";

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: index.php");
            exit();
        } else {
            $errors[] = "Неправильне ім'я користувача або пароль";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8" />
    <title>Вхід</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <form method="post" class="bg-white p-8 rounded shadow-md w-96">
        <h1 class="text-2xl font-bold mb-6 text-center">Вхід</h1>

        <?php if ($errors): ?>
            <div class="mb-4 text-red-600">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $e): ?>
                        <li><?=htmlspecialchars($e)?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <label class="block mb-2 font-semibold">Ім'я користувача</label>
        <input type="text" name="username" value="<?=htmlspecialchars($username ?? '')?>" required
            class="w-full mb-4 p-2 border rounded" />

        <label class="block mb-2 font-semibold">Пароль</label>
        <input type="password" name="password" required class="w-full mb-6 p-2 border rounded" />

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded font-semibold">Увійти</button>

        <p class="mt-4 text-center text-sm text-gray-600">Ще немає акаунта? <a href="register.php" class="text-blue-600 hover:underline">Зареєструватися</a></p>
    </form>
</body>
</html>
