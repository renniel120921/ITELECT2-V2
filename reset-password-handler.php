<?php
session_start();
include_once 'config/settings-configuration.php';

if (isset($_POST['btn-reset-password'])) {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: reset-password.php?token=$token");
        exit;
    }

    // Fetch reset token details
    $stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = :token LIMIT 1");
    $stmt->execute([':token' => $token]);

    if ($stmt->rowCount() === 1) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check token expiration
        if (strtotime($row['expires_at']) < time()) {
            $_SESSION['error'] = "Token has expired.";
            header("Location: reset-password.php?token=$token");
            exit;
        }

        $email = $row['email'];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Update user's password
        $update = $conn->prepare("UPDATE user SET password = :password WHERE email = :email");
        $update->execute([
            ':password' => $hashed_password,
            ':email' => $email
        ]);

        // Delete the token after successful reset
        $conn->prepare("DELETE FROM password_resets WHERE email = :email")->execute([':email' => $email]);

        $_SESSION['message'] = "Password has been reset. Please log in.";
        header("Location: login.php");
        exit;
    } else {
        $_SESSION['error'] = "Invalid or expired token.";
        header("Location: reset-password.php?token=$token");
        exit;
    }
}
?>
