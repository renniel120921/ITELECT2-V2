<?php
session_start();

// Generate CSRF token if not set (simple example)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include_once 'config/settings-configuration.php';

// Get reset token from URL
$token = $_GET['token'] ?? '';

// Stop if token is missing (optional)
if (empty($token)) {
    die('Reset token missing. Please use the link sent to your email.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md p-6 bg-white rounded-xl shadow-md space-y-6">
        <h1 class="text-2xl font-bold text-center text-gray-800">Reset Your Password</h1>
        <p class="text-center text-sm text-gray-600">Enter a new password for your account.</p>

        <form action="dashboard/admin/authentication/admin-class.php" method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="reset_token" value="<?= htmlspecialchars($token); ?>">

            <input type="password" name="new_password" placeholder="New Password" required
                class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-green-400" />

            <input type="password" name="confirm_password" placeholder="Confirm Password" required
                class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-green-400" />

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
