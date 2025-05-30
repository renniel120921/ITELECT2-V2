<?php
session_start();
require_once 'database/dbconnection.php';
require_once 'config/settings-configuration.php';
require_once "vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class PasswordReset
{
    private $conn;
    private $smtp_email;
    private $smtp_password;

    public function __construct()
    {
        $database = new Database();
        $this->conn =  $database->dbConnection();

        $settings = new SystemConfig();
        $this->smtp_email = $settings->getSmtpEmail();
        $this->smtp_password = $settings->getSmtpPassword();
    }

    public function sendOtp($email)
    {
        try {
            // Check if email exists in user table
            $stmt = $this->conn->prepare("SELECT * FROM user WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                echo "<script>alert('Email not found!'); window.location.href='forgot-password.php';</script>";
                exit;
            }

            // Generate OTP and expiration time (15 minutes)
            $otp = rand(100000, 999999);
            $expires_at = date("Y-m-d H:i:s", strtotime("+15 minutes"));

            // Delete previous OTP for this email
            $deleteStmt = $this->conn->prepare("DELETE FROM password_resets WHERE email = :email");
            $deleteStmt->execute([':email' => $email]);

            // Insert new OTP
            $insertStmt = $this->conn->prepare("INSERT INTO password_resets (email, otp, expires_at) VALUES (:email, :otp, :expires_at)");
            $insertStmt->execute([
                ':email' => $email,
                ':otp' => $otp,
                ':expires_at' => $expires_at
            ]);

            // Prepare email content
            $subject = "Password Reset OTP";
            $message = "
                <p>Hello,</p>
                <p>Your OTP for password reset is: <b>$otp</b></p>
                <p>This OTP is valid for 15 minutes.</p>
                <p>If you didn't request this, please ignore this email.</p>
            ";

            // Send email
            if ($this->send_email($email, $message, $subject)) {
                echo "<script>alert('OTP sent to your email!'); window.location.href='reset-password.php?email=" . urlencode($email) . "';</script>";
                exit;
            } else {
                echo "<script>alert('Failed to send OTP email. Please try again later.'); window.location.href='forgot-password.php';</script>";
                exit;
            }

        } catch (Exception $e) {
            echo "<script>alert('An error occurred. Please try again later.'); window.location.href='forgot-password.php';</script>";
            // Optionally log error $e->getMessage()
            exit;
        }
    }

    private function send_email($email, $message, $subject)
    {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->SMTPDebug = 0;
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = "tls";
            $mail->Host = "smtp.gmail.com";
            $mail->Port = 587;
            $mail->Username = $this->smtp_email;
            $mail->Password = $this->smtp_password;
            $mail->setFrom($this->smtp_email, "Your System");
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;
            return $mail->send();
        } catch (Exception $e) {
            // Optionally log $e->getMessage() for debugging
            return false;
        }
    }
}

if (isset($_POST['btn-forgot'])) {
    $email = trim($_POST['email']);
    $passwordReset = new PasswordReset();
    $passwordReset->sendOtp($email);
}
?>

<style>
  body {
    font-family: Arial, sans-serif;
    background: #f4f6f8;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
  }

  form {
    background: white;
    padding: 30px 40px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    width: 320px;
  }

  input[type="email"] {
    width: 100%;
    padding: 12px 14px;
    margin-bottom: 20px;
    border: 1.5px solid #ccc;
    border-radius: 5px;
    font-size: 15px;
    transition: border-color 0.3s ease;
  }

  input[type="email"]:focus {
    border-color: #007BFF;
    outline: none;
  }

  button[name="btn-forgot"] {
    width: 100%;
    padding: 12px 0;
    background-color: #007BFF;
    color: white;
    font-weight: 600;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s ease;
  }

  button[name="btn-forgot"]:hover {
    background-color: #0056b3;
  }
</style>

<form method="post" action="">
    <input type="email" name="email" placeholder="Enter your registered email" required>
    <button type="submit" name="btn-forgot">Send OTP</button>
</form>

