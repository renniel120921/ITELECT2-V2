<?php
session_start();
include_once 'config/settings-configuration.php';

if (isset($_POST['btn-forgot-password'])) {
    $email = trim($_POST['email']);

    // Check if user exists
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = :email");
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Generate token
        $token = bin2hex(random_bytes(50));
        $expires_at = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Remove existing token
        $conn->prepare("DELETE FROM password_resets WHERE email = :email")->execute([':email' => $email]);

        // Save token to DB
        $insert = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires_at)");
        $insert->execute([
            ':email' => $email,
            ':token' => $token,
            ':expires_at' => $expires_at
        ]);

        // Send email
        $reset_link = "http://localhost/ITELECT2-V2/reset-password.php?token=$token";
        $subject = "Reset Your Password";
        $message = "Click the link below to reset your password:<br><br>
        <a href='$reset_link'>$reset_link</a><br><br>
        This link will expire in 1 hour.";

        // Use mail() or PHPMailer here
        // mail($email, $subject, $message, $headers);

        $_SESSION['message'] = "Password reset link has been sent to your email.";
        header("Location: forgot-password.php");
        exit;
    } else {
        $_SESSION['error'] = "No account found with that email.";
        header("Location: forgot-password.php");
        exit;
    }
}
?>
