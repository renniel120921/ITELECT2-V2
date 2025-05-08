<?php
    include_once 'config/settings-configuration.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Reset Password</h2>

        <form action="#" method="POST" class="space-y-5">
            <div>
                <label for="new-password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                <input type="password" id="new-password" name="new_password" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label for="confirm-password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <input type="password" id="confirm-password" name="confirm_password" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2 rounded-xl hover:bg-indigo-700 transition">
                Reset Password
            </button>
        </form>

        <p class="text-center text-sm text-gray-600 mt-4">
            <a href="index.php" class="text-indigo-600 hover:underline">Back to Login</a>
        </p>
    </div>

</body>
</html>
