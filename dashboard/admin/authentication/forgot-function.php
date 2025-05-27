<?php
function sendPasswordReset($email) {
    // Include DB connection
    require_once '../../../database/dbconnection.php';

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        return "Email not found.";
    }

    // Generate token
    $token = bin2hex(random_bytes(32));
    $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

    // Save token to database (create a table if you don't have one)
    $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $token, $expires);
    $stmt->execute();

    // Send email (simplified version)
    $resetLink = "https://yourdomain.com/reset-password.php?token=" . $token;
    $subject = "Password Reset Request";
    $message = "Click the link to reset your password: $resetLink";
    $headers = "From: no-reply@yourdomain.com";

    if (mail($email, $subject, $message, $headers)) {
        return "Reset link sent.";
    } else {
        return "Failed to send email.";
    }
}
