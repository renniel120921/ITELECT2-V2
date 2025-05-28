<?php
session_start();

require '../../../vendor/autoload.php';  // Composer autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include_once '../../../database/dbconnection.php';
include_once '../../../config/settings-configuration.php';

// Initialize DB connection
$database = new Database();
$conn = $database->dbConnection();

// Enable PDO error reporting for debug
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['btn-forgot-password'])) {

    $email = trim($_POST['email'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';

    // CSRF check
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        die("⚠ Invalid CSRF token.");
    }

    if (empty($email)) {
        die("⚠ Please enter your email address.");
    }

    try {
        // Check if the email exists
        $stmt = $conn->prepare("SELECT id FROM user WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() !== 1) {
            die("⚠ No account found with that email.");
        }

        // Generate secure token
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime('+1 hour'));

        echo "🔍 Preparing to insert reset token for: $email<br>";

        // Delete any existing tokens
        $delete = $conn->prepare("DELETE FROM password_resets WHERE email = :email");
        $delete->execute([':email' => $email]);
        echo "🗑️ Old tokens deleted<br>";

        // Insert new reset token
        $insert = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires)");
        $success = $insert->execute([
            ':email'   => $email,
            ':token'   => $token,
            ':expires' => $expires
        ]);

        if (!$success) {
            die("❌ Failed to insert reset token.");
        }

        echo "✅ Token inserted successfully<br>";

        $reset_link = "http://localhost/ITELECT2-V2/reset-password.php?token=$token";

        // Setup PHPMailer
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'rennielsalazar948@gmail.com';        // Your Gmail
            $mail->Password = 'aift rzhk xzkb irnj';                // App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('rennielsalazar948@gmail.com', 'ITELECT2 Support');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = '🔐 Reset Your Password';
            $mail->Body = "
                <p>Hi,</p>
                <p>You requested a password reset. Click the button below to reset your password. This link will expire in 1 hour.</p>
                <p><a href='$reset_link' style='padding: 10px 15px; background-color: #007bff; color: white; text-decoration: none;'>Reset Password</a></p>
                <p>If you didn't request this, you can ignore this email.</p>
                <br><p>— ITELECT2 Support</p>
            ";

            $mail->send();
            echo "📧 Reset link sent to your email.";
        } catch (Exception $e) {
            echo "❌ Mailer Error: " . $mail->ErrorInfo;
        }

    } catch (PDOException $e) {
        die("❌ Database Error: " . $e->getMessage());
    }
}
