<?php
session_start();
include_once '../../../database/dbconnection.php'; // adjust path kung iba
include_once 'config/settings-configuration.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['btn-forgot-password'])) {

    $email = trim($_POST['email']);
    $csrf_token = $_POST['csrf_token'];

    // CSRF protection
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        die("Invalid CSRF token.");
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        // Email exists, generate token
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime('+1 hour'));

        // Save token to reset_tokens table (create if not existing)
        $insert = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $insert->bind_param("sss", $email, $token, $expires);
        $insert->execute();

        // Send email (pseudo-email for now)
        $reset_link = "http://yourdomain.com/reset-password.php?token=$token";
        // Use PHPMailer or mail() function in production
        mail($email, "Reset Your Password", "Click here to reset your password: $reset_link");

        echo "Reset link sent to your email.";
    } else {
        echo "No account found with that email.";
    }

    $stmt->close();
    $conn->close();
}
?>
