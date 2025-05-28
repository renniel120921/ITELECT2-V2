<?php
session_start();
require_once 'config/settings-configuration.php';
require_once 'database/dbconnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Invalid CSRF token.";
        header("Location: reset-password.php?token=" . $_POST['token']);
        exit;
    }

    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($token) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: reset-password.php?token=$token");
        exit;
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: reset-password.php?token=$token");
        exit;
    }

    $system = new SystemConfig();
    $stmt = $system->runQuery("SELECT * FROM password_resets WHERE token = :token AND expires_at > NOW()");
    $stmt->execute([':token' => $token]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        $_SESSION['error'] = "Invalid or expired token.";
        header("Location: reset-password.php?token=$token");
        exit;
    }

    $email = $data['email'];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $update = $system->runQuery("UPDATE user SET password = :password WHERE email = :email");
    $update->execute([
        ':password' => $hashedPassword,
        ':email' => $email
    ]);

    $system->runQuery("DELETE FROM password_resets WHERE email = :email")->execute([':email' => $email]);

    $_SESSION['message'] = "Password reset successful. Please login.";
    header("Location: login.php");
    exit;
}
?>
