<?php
session_start();
require_once 'database/dbconnection.php';

$db = new Database();
$conn = $db->dbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn-reset-password'])) {
    // CSRF token validation
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    // Get and sanitize inputs
    $token = filter_var($_POST['token'], FILTER_SANITIZE_STRING);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: reset-password.php?token=$token");
        exit();
    }

    // Check if token exists and not expired
    $stmt = $conn->prepare("SELECT email, expires_at FROM password_resets WHERE token = :token");
    $stmt->bindParam(":token", $token);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        $_SESSION['error'] = "Invalid or expired token.";
        header("Location: reset-password.php");
        exit();
    }

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $expires_at = $row['expires_at'];
    $email = $row['email'];

    // Check expiry
    if (strtotime($expires_at) < time()) {
        // Token expired, delete it
        $del = $conn->prepare("DELETE FROM password_resets WHERE token = :token");
        $del->bindParam(":token", $token);
        $del->execute();

        $_SESSION['error'] = "Token expired. Please request a new password reset.";
        header("Location: forgot-password.php");
        exit();
    }

    // Hash new password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Update user password
    $update = $conn->prepare("UPDATE user SET password = :password WHERE email = :email");
    $update->bindParam(":password", $hashed_password);
    $update->bindParam(":email", $email);
    $update->execute();

    // Delete the token after successful reset
    $del = $conn->prepare("DELETE FROM password_resets WHERE token = :token");
    $del->bindParam(":token", $token);
    $del->execute();

    $_SESSION['message'] = "Password successfully reset. You can now login.";
    header("Location: login.php");
    exit();
}
