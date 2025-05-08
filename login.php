<?php
    include_once 'config/settings-configuration.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-md">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">SIGN IN</h1>

        <form action="dashboard/admin/authentication/admin-class.php" method="POST" class="space-y-5">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="Enter Email" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Enter Password" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit" name="btn-signin"
                    class="w-full bg-blue-600 text-white py-2 rounded-xl hover:bg-blue-700 transition">
                SIGN IN
            </button>

            <div class="text-center text-sm text-gray-600 mt-4 space-y-1">
                <p><a href="forgot-password.php" class="text-blue-600 hover:underline">Forgot Password?</a></p>
                <p><a href="reset-password.php" class="text-blue-600 hover:underline">Reset Password</a></p>
                <p>Don't have an account? <a href="register.php" class="text-blue-600 hover:underline">Register here</a></p>
            </div>
        </form>
    </div>

</body>
</html>
