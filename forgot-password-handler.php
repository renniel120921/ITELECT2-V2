<?php
session_start();
include_once 'config/settings-configuration.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $csrf_token = $_POST['csrf_token'];

    // Validate CSRF token
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        die("Invalid CSRF token.");
    }

    // Check if email exists in the database
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Generate a unique token
        $token = bin2hex(random_bytes(50));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour')); // Set expiration to 1 hour from now

        // Optional: Remove existing tokens for the same email to avoid duplicates
        $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);

        // Insert new reset token with expiration
        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        if ($stmt->execute([$email, $token, $expires_at])) {
            // Send reset email
            $resetLink = "http://yourdomain.com/reset-password.php?token=" . $token;
            $subject = "Password Reset Request";
            $message = "Click the link to reset your password: " . $resetLink;
            $headers = "From: noreply@yourdomain.com\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            if (mail($email, $subject, $message, $headers)) {
                echo "A password reset link has been sent to your email.";
            } else {
                echo "Failed to send the reset email. Please try again.";
            }
        } else {
            echo "Failed to create a password reset request. Please try again.";
        }
    } else {
        echo "No account found with that email address.";
    }
}
?>
