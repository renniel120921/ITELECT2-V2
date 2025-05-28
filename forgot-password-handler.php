<?php
session_start();
require_once 'config/settings-configuration.php';
require_once 'database/dbconnection.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// CSRF validation
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = "Invalid CSRF token.";
    header("Location: reset-password.php?token=" . $_POST['token']);
    exit();
}

// Input validation
$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($token) || empty($password) || empty($confirm_password)) {
    $_SESSION['error'] = "All fields are required.";
    header("Location: reset-password.php?token=$token");
    exit();
}

if ($password !== $confirm_password) {
    $_SESSION['error'] = "Passwords do not match.";
    header("Location: reset-password.php?token=$token");
    exit();
}

// Use SystemConfig to get PDO connection
$systemConfig = new SystemConfig();
$stmt = $systemConfig->runQuery("SELECT * FROM password_resets WHERE token = :token AND expires_at > NOW()");
$stmt->execute([':token' => $token]);
$resetData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resetData) {
    $_SESSION['error'] = "Invalid or expired token.";
    header("Location: reset-password.php?token=$token");
    exit();
}

$email = $resetData['email'];
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Update password in `users` table
$updateStmt = $systemConfig->runQuery("UPDATE users SET password = :password WHERE email = :email");
$updateStmt->execute([
    ':password' => $hashed_password,
    ':email' => $email
]);

// Delete reset token after use
$deleteStmt = $systemConfig->runQuery("DELETE FROM password_resets WHERE email = :email");
$deleteStmt->execute([':email' => $email]);

$_SESSION['message'] = "Password has been reset successfully. Please log in with your new password.";
header("Location: login.php");
exit();
