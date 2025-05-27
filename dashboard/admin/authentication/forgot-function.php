<?php
function sendPasswordReset($email) {
    // Include DB connection
    require_once '../../../database/dbconnection.php';

    $database = new Database();
    $conn = $database->dbConnection();

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM user WHERE email = :email");
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        return "Email not found.";
    }

    // Generate token
    $token = bin2hex(random_bytes(32));
    $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

    // Save token to database
    $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires_at)");
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':token', $token, PDO::PARAM_STR);
    $stmt->bindValue(':expires_at', $expires, PDO::PARAM_STR);

    if (!$stmt->execute()) {
        return "Failed to save reset token.";
    }

    // Send email (simplified)
    $resetLink = "http://localhost/ITELECT2-V2/reset-password.php?token=" . $token;
    $subject = "Password Reset Request";
    $message = "Click the link to reset your password: $resetLink";
    $headers = "From: no-reply@yourdomain.com";

    if (mail($email, $subject, $message, $headers)) {
        return "Reset link sent.";
    } else {
        return "Failed to send email.";
    }
}
