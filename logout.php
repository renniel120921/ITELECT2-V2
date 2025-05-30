<?php
session_start();
require 'config/db.php';

if (isset($_SESSION['user_id'])) {
    // Log logout activity
    $stmt_log = $pdo->prepare("INSERT INTO logs (user_id, activity, created_at) VALUES (?, ?, NOW())");
    $stmt_log->execute([$_SESSION['user_id'], 'Logged out']);
}

session_destroy();
header("Location: login.php");
exit;
