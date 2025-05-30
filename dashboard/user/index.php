<?php
session_start();
require '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

$username = $_SESSION['username'];

?>

<!DOCTYPE html>
<html>
<head><title>User Dashboard</title></head>
<body>
<h2>Welcome, <?= htmlspecialchars($username) ?></h2>
<p>This is your dashboard.</p>
<a href="../../logout.php">Logout</a>
</body>
</html>
