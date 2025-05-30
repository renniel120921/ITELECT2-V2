<?php
session_start();
require_once 'dashboard/admin/authentication/admin-class.php';


// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$admin = new Admin();

// Get user info from DB (email, username)
$userId = $_SESSION['user_id'];
$query = "SELECT username, email FROM user WHERE id = :id LIMIT 1";
$stmt = $admin->conn->prepare($query);
$stmt->bindParam(':id', $userId);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // User not found, logout
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

$username = htmlspecialchars($user['username']);
$email = htmlspecialchars($user['email']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Dashboard - Welcome <?php echo $username; ?></title>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f4f6f8;
        margin: 0;
        padding: 0;
    }
    header {
        background: #007bff;
        color: white;
        padding: 1rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    header h1 {
        margin: 0;
        font-weight: normal;
    }
    header nav a {
        color: white;
        text-decoration: none;
        margin-left: 1.5rem;
        font-weight: 600;
    }
    main {
        max-width: 900px;
        margin: 2rem auto;
        background: white;
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgb(0 0 0 / 0.1);
    }
    .welcome {
        font-size: 1.3rem;
        margin-bottom: 1rem;
    }
    .userinfo {
        background: #e9ecef;
        padding: 1rem;
        border-radius: 5px;
        margin-bottom: 2rem;
        color: #333;
    }
    .userinfo strong {
        display: inline-block;
        width: 90px;
    }
    footer {
        text-align: center;
        padding: 1rem 0;
        color: #777;
        font-size: 0.9rem;
    }
</style>
</head>
<body>

<header>
    <h1>Dashboard</h1>
    <nav>
        <a href="dashboard.php">Home</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main>
    <div class="welcome">Welcome back, <strong><?php echo $username; ?></strong>!</div>

    <div class="userinfo">
        <p><strong>Email:</strong> <?php echo $email; ?></p>
        <p><strong>Username:</strong> <?php echo $username; ?></p>
        <!-- Add more user info here if needed -->
    </div>

    <section>
        <h2>Your Dashboard</h2>
        <p>This is a simple dashboard template with a clean UI.</p>
        <p>You can add your components, charts, tables, and other widgets here.</p>
    </section>
</main>

<footer>
    &copy; <?php echo date('Y'); ?> Your Company. All rights reserved.
</footer>

</body>
</html>
