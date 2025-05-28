<?php
session_start();
require_once 'config/settings-configuration.php';
require_once 'database/dbconnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (empty($_POST['csrf_token']) || ($_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? ''))) {
        $_SESSION['error'] = "Invalid CSRF token.";
        header("Location: reset-password.php?token=" . urlencode($_POST['token'] ?? ''));
        exit;
    }

    // Retrieve and sanitize inputs
    $token = trim($_POST['token'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate required fields
    if (empty($token) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: reset-password.php?token=" . urlencode($token));
        exit;
    }

    // Check password confirmation
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: reset-password.php?token=" . urlencode($token));
        exit;
    }

    $system = new SystemConfig();

    // Verify token and check if it has expired
    $stmt = $system->runQuery("SELECT email FROM password_resets WHERE token = :token AND expires_at > NOW()");
    $stmt->execute([':token' => $token]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        $_SESSION['error'] = "Invalid or expired token.";
        header("Location: reset-password.php?token=" . urlencode($token));
        exit;
    }

    $email = $data['email'];

    // Hash the new password securely
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Update user password in database
    $update = $system->runQuery("UPDATE user SET password = :password WHERE email = :email");
    $update->execute([
        ':password' => $hashedPassword,
        ':email' => $email
    ]);

    // Delete the used reset token so it can't be reused
    $system->runQuery("DELETE FROM password_resets WHERE email = :email")->execute([':email' => $email]);

    // Clear CSRF token after successful reset
    unset($_SESSION['csrf_token']);

    // Success message and redirect to login
    $_SESSION['message'] = "Password reset successful. Please login.";
    header("Location: login.php");
    exit;
}
