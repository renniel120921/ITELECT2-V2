<?php
    include_once 'config/settings-configuration.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In / Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md p-6 bg-white rounded-xl shadow-md space-y-6">
        <!-- SIGN IN -->
        <div>
            <h1 class="text-2xl font-bold text-center mb-4">SIGN IN</h1>
            <form action="dashboard/admin/authentication/admin-class.php" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?=$_SESSION["csrf_token"]; ?>">

                <input type="email" name="email" placeholder="Enter Email" required
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">

                <input type="password" name="password" placeholder="Enter Password" required
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">

                <button type="submit" name="btn-signin"
                    class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition">SIGN IN</button>
            </form>
        </div>

        <!-- REGISTRATION -->
        <div>
            <h1 class="text-2xl font-bold text-center mb-4">REGISTRATION</h1>
            <form action="dashboard/admin/authentication/admin-class.php" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION["csrf_token"]; ?>">

                <input type="text" name="username" placeholder="Enter Username" required
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-green-400">

                <input type="email" name="email" placeholder="Enter Email" required
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-green-400">

                <input type="password" name="password" placeholder="Enter Password" required
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-green-400">

                <button type="submit" name="btn-signup"
                    class="w-full bg-green-600 text-white py-2 rounded-md hover:bg-green-700 transition">SIGN UP</button>
            </form>
        </div>
    </div>

</body>
</html>
