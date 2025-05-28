<?php
session_start();
require_once 'config/settings-configuration.php';
require_once 'database/dbconnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Invalid CSRF token.";
        header("Location: forgot-password.php");
        exit;
    }

    $email = $_POST['email'];
    $token = bin2hex(random_bytes(32));
    $expires_at = date("Y-m-d H:i:s", strtotime("+1 hour"));

    $system = new SystemConfig();
    $stmt = $system->runQuery("INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires)
        ON DUPLICATE KEY UPDATE token = :token, expires_at = :expires");

    $stmt->execute([
        ':email' => $email,
        ':token' => $token,
        ':expires' => $expires_at
    ]);

    // Sample email logic (replace with actual PHPMailer or mail function)
    $resetLink = 'http://localhost/ITELECT2-V2/reset-password.php?token=$token';
    // mail($email, "Reset Password", "Click this link: $resetLink");

    $_SESSION['message'] = "Reset link has been sent to your email.";
    header("Location: forgot-password.php");
    exit;
}
?>
