<?php
session_start();
include_once 'config/settings-configuration.php';

if (!isset($_POST['btn-forgot-password'])) {
    header("Location: forgot-password.php");
    exit;
}

// CSRF check
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Invalid CSRF token");
}

$email = trim($_POST['email']);

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email address");
}

// Check if email exists in users table
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    // For security, don't reveal if email doesn't exist, just pretend
    $_SESSION['message'] = "If your email exists in our system, a reset link has been sent.";
    header("Location: forgot-password.php");
    exit;
}

$stmt->close();

// Generate secure token
$token = bin2hex(random_bytes(32));
$expires = date("Y-m-d H:i:s", strtotime('+1 hour'));

// Delete old tokens for this email
$del = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
$del->bind_param("s", $email);
$del->execute();
$del->close();

// Insert new token
$insert = $conn->prepare("INSERT INTO password_resets (email, token, expires_at, created_at) VALUES (?, ?, ?, NOW())");
$insert->bind_param("sss", $email, $token, $expires);
$insert->execute();
$insert->close();

// Prepare reset URL (adjust domain accordingly)
$reset_url = "https://yourdomain.com/reset-password.php?token=" . $token;

// TODO: Send email with reset URL
// Use your mail system here, e.g. PHP mail() or PHPMailer
// Example:
// mail($email, "Password Reset", "Click here to reset: $reset_url");

// Feedback message
$_SESSION['message'] = "If your email exists in our system, a reset link has been sent.";
header("Location: forgot-password.php");
exit;
?>
