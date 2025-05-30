<?php
session_start();
require_once 'config/db.php';  // your PDO connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $_SESSION['error'] = "Please enter email and password.";
        header("Location: login_form.php");
        exit;
    }

    // Fetch user with verified status = 1
    $stmt = $pdo->prepare("SELECT id, username, password FROM user WHERE email = ? AND status = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['error'] = "Invalid email or account not verified.";
        header("Location: login_form.php");
        exit;
    }

    // Verify password hash
    if (password_verify($password, $user['password'])) {
        // Set session for logged-in user
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        // Log the login activity
        $stmtLog = $pdo->prepare("INSERT INTO logs (user_id, activity, created_at) VALUES (?, ?, NOW())");
        $stmtLog->execute([$user['id'], 'User logged in']);

        header("Location: dashboard/index.php");  // Adjust if your dashboard path differs
        exit;
    } else {
        $_SESSION['error'] = "Incorrect password.";
        header("Location: login_form.php");
        exit;
    }
} else {
    header("Location: login_form.php");
    exit;
}
