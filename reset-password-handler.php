<?php
session_start();
include_once 'config/settings-configuration.php'; // dito dapat naka-setup $pdo or mysqli connection

// Check request method and button
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['btn-reset-password'])) {
    header('Location: forgot-password.php');
    exit;
}

// CSRF check
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid CSRF token');
}

// Get inputs safely
$token = $_POST['reset_token'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Basic validation
if (!$token || !$new_password || !$confirm_password) {
    die('All fields are required.');
}

if ($new_password !== $confirm_password) {
    die('Passwords do not match.');
}

// Password strength check (basic example, customize as needed)
if (strlen($new_password) < 8) {
    die('Password must be at least 8 characters.');
}

try {
    // Find user by reset token and check expiry
    $stmt = $pdo->prepare("SELECT id, reset_token_expiry FROM users WHERE reset_token = :token LIMIT 1");
    $stmt->execute(['token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die('Invalid or expired reset token.');
    }

    // Check if token expired
    if (strtotime($user['reset_token_expiry']) < time()) {
        die('Reset token has expired. Please request a new password reset.');
    }

    // Hash the new password (use password_hash, no plain text)
    $passwordHash = password_hash($new_password, PASSWORD_DEFAULT);

    // Update user's password and clear the reset token and expiry
    $update = $pdo->prepare("UPDATE users SET password = :password, reset_token = NULL, reset_token_expiry = NULL WHERE id = :id");
    $update->execute([
        'password' => $passwordHash,
        'id' => $user['id']
    ]);

    echo "Password has been successfully reset. You can now <a href='login.php'>login</a>.";
} catch (Exception $e) {
    // You can log $e->getMessage() somewhere
    die('An error occurred. Please try again later.');
}
