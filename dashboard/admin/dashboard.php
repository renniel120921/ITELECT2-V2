<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

// CSRF Token Setup
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Optional: Store username in session during login for display
$username = $_SESSION['username'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Dashboard</title>
<style>
    /* Reset */
    * {
        margin: 0; padding: 0; box-sizing: border-box;
    }
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #121212;
        color: #eee;
        display: flex;
        min-height: 100vh;
    }

    /* Sidebar */
    nav.sidebar {
        width: 220px;
        background-color: #1f1f1f;
        padding: 20px;
        box-shadow: 2px 0 12px rgba(0,255,144,0.25);
        display: flex;
        flex-direction: column;
    }
    nav.sidebar h2 {
        color: #00ff90;
        margin-bottom: 30px;
        text-align: center;
    }
    nav.sidebar a {
        color: #eee;
        text-decoration: none;
        padding: 12px 15px;
        margin-bottom: 10px;
        border-radius: 5px;
        transition: background-color 0.3s;
    }
    nav.sidebar a:hover {
        background-color: #00ff90;
        color: #121212;
    }
    nav.sidebar a.logout {
        margin-top: auto;
        background-color: #ff3b3b;
        text-align: center;
    }
    nav.sidebar a.logout:hover {
        background-color: #cc2a2a;
        color: #fff;
    }

    /* Main Content */
    main.content {
        flex-grow: 1;
        padding: 30px;
    }
    main.content h1 {
        margin-bottom: 20px;
        color: #00ff90;
    }
    main.content p {
        font-size: 18px;
        line-height: 1.6;
    }

    /* Responsive */
    @media (max-width: 768px) {
        body {
            flex-direction: column;
        }
        nav.sidebar {
            width: 100%;
            flex-direction: row;
            overflow-x: auto;
        }
        nav.sidebar a {
            flex: 1 0 auto;
            margin: 0 10px 0 0;
            text-align: center;
        }
        nav.sidebar a.logout {
            margin-left: auto;
        }
    }
</style>
</head>
<body>
    <nav class="sidebar">
        <h2>MyApp Dashboard</h2>
        <a href="dashboard.php">Home</a>
        <a href="profile.php">Profile</a>
        <a href="settings.php">Settings</a>
        <a href="reports.php">Reports</a>
        <a href="../../logout.php" class="logout">Logout</a>
    </nav>

    <main class="content">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
        <p>This is your dashboard where you can manage your account, view reports, and update settings.</p>

        <!-- Example secured form (if needed in future) -->
        <!--
        <form method="POST" action="some-action.php">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <button type="submit">Secure Action</button>
        </form>
        -->
    </main>
</body>
</html>
