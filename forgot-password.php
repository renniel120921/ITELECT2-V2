<?php
session_start();
include_once 'config/settings-configuration.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
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

    <form action="forgot-password-handler.php" method="POST" class="w-full max-w-md p-6 bg-white rounded-xl shadow space-y-4">
        <h1 class="text-2xl font-bold text-center">Forgot Password</h1>
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="email" name="email" required placeholder="Enter your email" class="w-full px-4 py-2 border rounded-md">
        <button name="btn-forgot-password" class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700">Send Reset Link</button>
        <div class="text-center"><a href="login.php" class="text-blue-500 text-sm">Back to Login</a></div>
    </form>
</body>
</html>
