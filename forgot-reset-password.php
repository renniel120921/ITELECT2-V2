<?php
    session_start();
    include_once 'config/settings-configuration.php';

    $token = $_GET['token'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $token ? 'Reset Password' : 'Forgot Password'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md p-6 bg-white rounded-xl shadow-md space-y-6">
        <?php if (!$token): ?>
            <!-- FORGOT PASSWORD FORM -->
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

        <?php else: ?>
            <!-- RESET PASSWORD FORM -->
            <h1 class="text-2xl font-bold text-center text-gray-800">Reset Your Password</h1>
            <p class="text-center text-sm text-gray-600">Enter a new password for your account.</p>

            <form action="reset-password-handler.php" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="reset_token" value="<?= htmlspecialchars($token); ?>">

                <input type="password" name="new_password" placeholder="New Password" required
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-green-400">

                <input type="password" name="confirm_password" placeholder="Confirm Password" required
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-green-400">

                <button type="submit" name="btn-reset-password"
                    class="w-full bg-green-600 text-white py-2 rounded-md hover:bg-green-700 transition">
                    Reset Password
                </button>
            </form>
        <?php endif; ?>

        <div class="text-center">
            <a href="login.php" class="text-sm text-blue-600 hover:underline">Back to Sign In</a>
        </div>
    </div>

</body>
</html>
