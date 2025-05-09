<?php
    include_once 'config/settings-configuration.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md p-6 bg-white rounded-xl shadow-md space-y-6">
        <h1 class="text-2xl font-bold text-center text-gray-800">Enter OTP</h1>
        <p class="text-center text-sm text-gray-600">We've sent a one-time password (OTP) to your registered email or phone.</p>

        <form action="dashboard/admin/authentication/admin-class.php" method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

            <input type="number" name="otp" placeholder="Enter OTP" required
                class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-purple-400">

            <button type="submit" name="btn-verify"
                class="w-full bg-purple-600 text-white py-2 rounded-md hover:bg-purple-700 transition">
                VERIFY
            </button>
        </form>

        <div class="text-center">
            <a href="index.php" class="text-sm text-blue-600 hover:underline">Back to Sign In</a>
        </div>
    </div>

</body>
</html>
