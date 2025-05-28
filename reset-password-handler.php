<?php
session_start();
require_once 'database/dbconnection.php';

$db = new Database();
$conn = $db->dbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn-reset-password'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: reset-password.php?token=$token");
        exit();
    }

    // Check token validity
    $stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = :token AND token_expiry >= NOW()");
    $stmt->bindParam(":token", $token);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $email = $row['email'];

        // Hash the new password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Update user's password
        $updateUser = $conn->prepare("UPDATE user SET password = :password WHERE email = :email");
        $updateUser->bindParam(":password", $password_hash);
        $updateUser->bindParam(":email", $email);
        $updateUser->execute();

        // Delete the used token
        $del = $conn->prepare("DELETE FROM password_resets WHERE email = :email");
        $del->bindParam(":email", $email);
        $del->execute();

        $_SESSION['message'] = "Password reset successful. You can now log in.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['error'] = "Invalid or expired token.";
        header("Location: forgot-password.php");
        exit();
    }
}
