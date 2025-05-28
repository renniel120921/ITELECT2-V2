<?php
ssession_start();
require_once 'config/settings-configuration.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Make sure PHPMailer is installed via Composer

include_once 'config/settings-configuration.php';

// Create DB connection
$database = new Database();
$pdo = $database->dbConnection();

// CSRF token check (basic example)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }

    if (isset($_POST['btn-forgot-password'])) {
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        if (!$email) {
            die('Invalid email address.');
        }

        // Check if email exists in your users table
        $stmt = $pdo->prepare("SELECT id, email FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            die('No user found with that email.');
        }

        // Generate a secure token & expiration (e.g. 1 hour)
        $token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Save token & expiry to DB (you need a reset_tokens table or add columns to users)
        $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)");
        $stmt->execute([
            'user_id' => $user['id'],
            'token' => $token,
            'expires_at' => $expires_at,
        ]);

        // Prepare email
        $systemConfig = new SystemConfig();
        $smtpEmail = $systemConfig->getSmtpEmail();
        $smtpPassword = $systemConfig->getSmtpPassword();

        $mail = new PHPMailer(true);

        try {
            // SMTP config
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Or your SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = $smtpEmail;
            $mail->Password = $smtpPassword;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom($smtpEmail, 'Your Website');
            $mail->addAddress($user['email']);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';

            $resetLink = "http://yourdomain.com/reset-password.php?token=$token";
            $mail->Body = "
                <p>Hello,</p>
                <p>You requested a password reset. Click the link below to reset your password:</p>
                <p><a href='$resetLink'>$resetLink</a></p>
                <p>If you did not request this, ignore this email.</p>
            ";

            $mail->send();

            echo 'Reset link sent! Check your email.';

        } catch (Exception $e) {
            echo "Mailer Error: " . $mail->ErrorInfo;
        }
    }
}
