<?php
require_once 'authentication/admin-class.php';

$admin = new ADMIN();

if (!$admin->isUserLoggedIn()) {
    header('Location: ../../login.php');
    exit;
}

$stmt = $admin->runQuery("SELECT * FROM user WHERE id = :id");
$stmt->execute([':id' => $_SESSION['adminSession']]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-blue-800 text-white flex flex-col">
            <div class="p-6 text-2xl font-bold border-b border-blue-700">
                Admin Panel
            </div>
            <nav class="flex-1 p-4 space-y-3">
                <a href="#" class="block px-4 py-2 rounded hover:bg-blue-700">Dashboard</a>
                <a href="#" class="block px-4 py-2 rounded hover:bg-blue-700">Users</a>
                <a href="#" class="block px-4 py-2 rounded hover:bg-blue-700">Reports</a>
                <a href="#" class="block px-4 py-2 rounded hover:bg-blue-700">Settings</a>
            </nav>
            <form action="authentication/admin-class.php" method="GET" class="p-4 border-t border-blue-700">
                <input type="hidden" name="admin_signout" value="1">
                <button type="submit"
                    class="w-full bg-red-600 hover:bg-red-700 transition text-white py-2 rounded">
                    Sign Out
                </button>
            </form>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8 overflow-y-auto">
            <h1 class="text-3xl font-bold mb-6">Welcome,
                <span class="text-blue-700"><?= htmlspecialchars($user_data['email']) ?></span>
            </h1>

            <!-- Dashboard Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white rounded shadow p-6">
                    <h2 class="text-lg font-semibold mb-2">Total Users</h2>
                    <p class="text-3xl font-bold text-blue-600">1,204</p>
                </div>

                <div class="bg-white rounded shadow p-6">
                    <h2 class="text-lg font-semibold mb-2">New Signups</h2>
                    <p class="text-3xl font-bold text-green-600">87</p>
                </div>

                <div class="bg-white rounded shadow p-6">
                    <h2 class="text-lg font-semibold mb-2">Reports Today</h2>
                    <p class="text-3xl font-bold text-red-600">12</p>
                </div>
            </div>
        </main>
    </div>

</body>
</html>
