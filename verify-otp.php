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
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md">
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">Enter OTP</h1>

        <form action="dashboard/admin/authentication/admin-class.php" method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

            <div>
                <label for="otp" class="block text-sm font-medium text-gray-700">OTP Code</label>
                <input
                    type="number"
                    name="otp"
                    id="otp"
                    placeholder="Enter OTP"
                    required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
            </div>

            <button
                type="submit"
                name="btn-verify"
                class="w-full bg-blue-600 text-white py-2 px-4 rounded-xl hover:bg-blue-700 transition"
            >
                VERIFY
            </button>
        </form>
    </div>

</body>
</html>
