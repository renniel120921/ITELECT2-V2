<?php
session_start();
include_once 'config/settings-configuration.php';

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <!-- Toast Notification -->
    <?php if (isset($_SESSION['message']) || isset($_SESSION['error'])): ?>
        <div id="toast" class="fixed top-5 right-5 z-50 px-5 py-3 rounded-md shadow-lg
            <?= isset($_SESSION['message']) ? 'bg-green-500 text-white' : 'bg-red-500 text-white' ?>">
            <?= $_SESSION['message'] ?? $_SESSION['error']; ?>
        </div>
        <script>
            setTimeout(() => {
                const toast = document.getElementById("toast");
                if (toast) toast.remove();
            }, 3000);
        </script>
        <?php unset($_SESSION['message'], $_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Form Container -->
    <div class="w-full max-w-md p-6 bg-white rounded-xl shadow-md space-y-6">
        <h1 class="text-2xl font-bold text-center text-gray-800">Forgot Password</h1>
        <p class="text-center text-sm text-gray-600">Enter your email and we'll send you a link to reset your password.</p>

        <form action="forgot-password-handler.php" method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

            <input type="email" name="email" placeholder="Enter your email address" required
                class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">

            <button type="submit" name="btn-forgot-password"
                class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition">
                Send Reset Link
            </button>
        </form>

        <div class="text-center">
            <a href="login.php" class="text-sm text-blue-600 hover:underline">Back to Sign In</a>
        </div>
    </div>

</body>
</html>
