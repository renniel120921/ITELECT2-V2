<?php
require_once 'authentication/admin-class.php';

$admin = new ADMIN();

if (!$admin->isUserLoggedIn()) {
    header('Location: ../../index.php');
    exit;
}

// Use consistent session key
$stmt = $admin->runQuery("SELECT * FROM user WHERE id = :id");
$stmt->execute([':id' => $_SESSION['adminSession']]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADMIN DASHBOARD</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-md text-center">
        <h1 class="text-2xl font-bold mb-4">Welcome, <span class="text-blue-600"><?= htmlspecialchars($user_data['email']) ?></span></h1>

        <form action="authentication/admin-class.php" method="GET">
            <input type="hidden" name="admin_signout" value="1">
            <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700 transition">
                Sign Out
            </button>
        </form>
    </div>
</body>
</html>
