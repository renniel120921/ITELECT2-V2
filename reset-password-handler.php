<?php
session_start();
include_once 'config/settings-configuration.php';

if (!isset($_POST['btn-reset-password'])) {
    header("Location: forgot-password.php");
    exit;
}

// CSRF check
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Invalid CSRF token");
}

$token = $_POST['reset_token'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($token) || empty($new_password) || empty($confirm_password)) {
    die("All fields are required.");
}

if ($new_password !== $confirm_password) {
    die("Passwords do not match.");
}

// Validate password length, etc.
if (strlen($new_password) < 8) {
    die("Password must be at least 8 characters.");
}

// Lookup token
$stmt = $conn->prepare("SELECT email, expires_at FROM password_resets WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Invalid or expired token.");
}

$row = $result->fetch_assoc();
if (strtotime($row['expires_at']) < time()) {
    die("Token expired. Please request a new password reset.");
}

$email = $row['email'];

// Hash new password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Update user's password
$update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
$update->bind_param("ss", $hashed_password, $email);
$update->execute();
$update->close();

// Delete password reset token to invalidate it
$del = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
$del->bind_param("s", $token);
$del->execute();
$del->close();

$_SESSION['message'] = "Your password has been reset successfully.";
header("Location: login.php");
exit;
?>
