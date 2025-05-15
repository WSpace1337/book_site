<?php
session_start();
require 'db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (!$username) $errors[] = "Введіть ім'я користувача";
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Введіть коректний email";
    if (!$password) $errors[] = "Введіть пароль";
    if ($password !== $password_confirm) $errors[] = "Паролі не співпадають";

    // Перевірка унікальності username та email
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Ім'я користувача або email вже зайняті";
        }
    }

    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $password_hash]);
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['username'] = $username;
        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8" />
    <title>Реєстрація</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <form method="post" class="bg-white p-8 rounded shadow-md w-96">
        <h1 class="text-2xl font-bold mb-6 text-center">Реєстрація</h1>

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

        <label class="block mb-2 font-semibold">Email</label>
        <input type="email" name="email" value="<?=htmlspecialchars($email ?? '')?>" required
            class="w-full mb-4 p-2 border rounded" />

        <label class="block mb-2 font-semibold">Пароль</label>
        <input type="password" name="password" required class="w-full mb-4 p-2 border rounded" />

        <label class="block mb-2 font-semibold">Підтвердження пароля</label>
        <input type="password" name="password_confirm" required class="w-full mb-6 p-2 border rounded" />

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded font-semibold">Зареєструватися</button>

        <p class="mt-4 text-center text-sm text-gray-600">Вже маєте акаунт? <a href="login.php" class="text-blue-600 hover:underline">Увійти</a></p>
    </form>
</body>
</html>
