<?php
session_start();
require_once 'database/dbconnection.php';

$db = new Database();
$conn = $db->dbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn-reset-password'])) {

    // Validate CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    // Get and sanitize input
    $token = filter_var($_POST['token'], FILTER_SANITIZE_STRING);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: reset-password.php?token=$token");
        exit();
    }

    // Check token validity and expiry
    $stmt = $conn->prepare("SELECT email, expires_at FROM password_resets WHERE token = :token LIMIT 1");
    $stmt->bindParam(':token', $token);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if token expired
        if (new DateTime() > new DateTime($row['expires_at'])) {
            $_SESSION['error'] = "Token expired. Please request a new password reset.";
            header("Location: forgot-password.php");
            exit();
        }

        $email = $row['email'];

        // Hash the new password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Update the user's password
        $update = $conn->prepare("UPDATE user SET password = :password WHERE email = :email");
        $update->bindParam(':password', $passwordHash);
        $update->bindParam(':email', $email);
        $update->execute();

        // Delete the used token
        $delete = $conn->prepare("DELETE FROM password_resets WHERE token = :token");
        $delete->bindParam(':token', $token);
        $delete->execute();

        $_SESSION['message'] = "Your password has been reset successfully. You can now log in.";
        header("Location: login.php");
        exit();

    } else {
        $_SESSION['error'] = "Invalid or expired token.";
        header("Location: forgot-password.php");
        exit();
    }
}
?>
