<?php
session_start();
include_once 'config/settings-configuration.php';

// Load Composer's autoloader (if using Composer)
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn-forgot-password'])) {

    // CSRF check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed.');
    }

    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die('Invalid email address.');
    }

    $systemConfig = new SystemConfig();

    // Check if email exists in users table
    $stmt = $systemConfig->runQuery("SELECT id FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die('Email not found.');
    }

    $userId = $user['id'];

    // Generate a unique token and expiry time (1 hour)
    $token = bin2hex(random_bytes(16));
    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Delete any existing tokens for this user
    $stmt = $systemConfig->runQuery("DELETE FROM password_resets WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $userId]);

    // Insert new token into password_resets table
    $stmt = $systemConfig->runQuery("INSERT INTO password_resets (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)");
    $stmt->execute([
        ':user_id' => $userId,
        ':token' => $token,
        ':expires_at' => $expires_at
    ]);

    // Prepare the reset link
    $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password.php?token=" . $token;

    // Send email with PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.example.com';    // Replace with your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = $systemConfig->getSmtpEmail();  // Your SMTP email from your config
        $mail->Password   = $systemConfig->getSmtpPassword(); // Your SMTP password from your config
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // Or PHPMailer::ENCRYPTION_SMTPS for SSL
        $mail->Port       = 587;  // Adjust SMTP port if necessary (587 for TLS, 465 for SSL)

        // Recipients
        $mail->setFrom($systemConfig->getSmtpEmail(), 'YourAppName');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body    = "
            <p>Hi,</p>
            <p>Click the link below to reset your password:</p>
            <p><a href='{$resetLink}'>Reset Password</a></p>
            <p>This link will expire in 1 hour.</p>
        ";

        $mail->send();
        echo 'Password reset link has been sent to your email.';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }

} else {
    header('Location: forgot-password.php');
    exit();
}
?>
