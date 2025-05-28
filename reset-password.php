<?php
session_start();
include_once 'config/settings-configuration.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$token = $_GET['token'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <?php if (isset($_SESSION['message']) || isset($_SESSION['error'])): ?>
        <div class="fixed top-5 right-5 px-5 py-3 rounded-md shadow-lg <?= isset($_SESSION['message']) ? 'bg-green-500' : 'bg-red-500' ?> text-white">
            <?= $_SESSION['message'] ?? $_SESSION['error']; ?>
        </div>
        <script>setTimeout(() => document.querySelector('div').remove(), 3000);</script>
        <?php unset($_SESSION['message'], $_SESSION['error']); ?>
    <?php endif; ?>

    <form action="reset-password-handler.php" method="POST" class="w-full max-w-md p-6 bg-white rounded-xl shadow space-y-4">
        <h1 class="text-2xl font-bold text-center">Reset Password</h1>
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <input type="password" name="password" placeholder="New Password" required class="w-full px-4 py-2 border rounded-md">
        <input type="password" name="confirm_password" placeholder="Confirm Password" required class="w-full px-4 py-2 border rounded-md">
        <button name="btn-reset-password" class="w-full bg-green-600 text-white py-2 rounded-md hover:bg-green-700">Reset</button>
    </form>
</body>
</html>
