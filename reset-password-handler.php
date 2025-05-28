<?php
session_start();
include_once 'config/settings-configuration.php';

require 'vendor/autoload.php'; // PHPMailer autoload
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn-reset-password'])) {

    // CSRF check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed.');
    }

    $token = $_POST['reset_token'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($token) || empty($newPassword) || empty($confirmPassword)) {
        die('All fields are required.');
    }

    if ($newPassword !== $confirmPassword) {
        die('Passwords do not match.');
    }

    if (strlen($newPassword) < 8) {
        die('Password must be at least 8 characters.');
    }

    $systemConfig = new SystemConfig();

    // Find the token in password_resets and check expiry
    $stmt = $systemConfig->runQuery("SELECT user_id, expires_at FROM password_resets WHERE token = :token LIMIT 1");
    $stmt->execute([':token' => $token]);
    $resetData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$resetData) {
        die('Invalid or expired reset token.');
    }

    if (strtotime($resetData['expires_at']) < time()) {
        // Token expired, delete it
        $stmt = $systemConfig->runQuery("DELETE FROM password_resets WHERE token = :token");
        $stmt->execute([':token' => $token]);
        die('Reset token has expired.');
    }

    $userId = $resetData['user_id'];

    // Hash the new password
    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update user's password in users table
    $stmt = $systemConfig->runQuery("UPDATE users SET password = :password WHERE id = :user_id");
    $stmt->execute([':password' => $passwordHash, ':user_id' => $userId]);

    // Delete the used token
    $stmt = $systemConfig->runQuery("DELETE FROM password_resets WHERE token = :token");
    $stmt->execute([':token' => $token]);

    // Optional: Send confirmation email to user that password was changed
    $stmt = $systemConfig->runQuery("SELECT email FROM users WHERE id = :user_id LIMIT 1");
    $stmt->execute([':user_id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $userEmail = $user['email'];

    if ($userEmail) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.example.com'; // change this
            $mail->SMTPAuth   = true;
            $mail->Username   = $systemConfig->getSmtpEmail();
            $mail->Password   = $systemConfig->getSmtpPassword();
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom($systemConfig->getSmtpEmail(), 'YourAppName');
            $mail->addAddress($userEmail);

            $mail->isHTML(true);
            $mail->Subject = 'Your Password Has Been Changed';
            $mail->Body    = "<p>Hello,</p><p>Your password has been successfully changed.</p><p>If you did not perform this action, please contact support immediately.</p>";

            $mail->send();
        } catch (Exception $e) {
            // Log this error or handle it, but don't block user
        }
    }

    echo "Your password has been reset successfully. <a href='login.php'>Login now</a>";
} else {
    header('Location: reset-password.php');
    exit();
}
