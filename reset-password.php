<?php
session_start(); // Needed for CSRF token

include_once 'config/settings-configuration.php';

$token = $_GET['token'] ?? '';

// Generate CSRF token if not yet set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<!-- (same HTML head) -->
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md p-6 bg-white rounded-xl shadow-md space-y-6">
        <h1 class="text-2xl font-bold text-center text-gray-800">Reset Your Password</h1>
        <p class="text-center text-sm text-gray-600">Enter a new password for your account.</p>

        <form action="reset-handler.php" method="POST" class="space-y-4">
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

        <div class="text-center">
            <a href="index.php" class="text-sm text-blue-600 hover:underline">Back to Sign In</a>
        </div>
    </div>
</body>
</html>
