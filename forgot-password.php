<?php
session_start();
require_once __DIR__.'/../../../database/dbconnection.php';
require_once __DIR__.'/../../../config/settings-configuration.php';
require_once __DIR__."/../../../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;

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
        // Check if email exists in user table
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo "<script>alert('Email not found!'); window.location.href='forgot-password.php';</script>";
            exit;
        }

        // Generate OTP
        $otp = rand(100000, 999999);

        // Save OTP and expiration (e.g., 15 minutes from now) in DB - create a password_resets table for this
        $expires_at = date("Y-m-d H:i:s", strtotime("+15 minutes"));

        // Delete previous OTP for this email first to avoid duplicates
        $this->conn->prepare("DELETE FROM password_resets WHERE email = :email")->execute([':email' => $email]);

        // Insert new OTP
        $stmt = $this->conn->prepare("INSERT INTO password_resets (email, otp, expires_at) VALUES (:email, :otp, :expires_at)");
        $stmt->execute([
            ':email' => $email,
            ':otp' => $otp,
            ':expires_at' => $expires_at
        ]);

        // Send OTP email
        $subject = "Password Reset OTP";
        $message = "
            <p>Hello,</p>
            <p>Your OTP for password reset is: <b>$otp</b></p>
            <p>This OTP is valid for 15 minutes.</p>
            <p>If you didn't request this, please ignore.</p>
        ";

        $this->send_email($email, $message, $subject);

        echo "<script>alert('OTP sent to your email!'); window.location.href='reset-password.php?email=$email';</script>";
    }

    private function send_email($email, $message, $subject)
    {
        $mail = new PHPMailer();
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
        $mail->send();
    }
}

if (isset($_POST['btn-forgot'])) {
    $email = trim($_POST['email']);
    $passwordReset = new PasswordReset();
    $passwordReset->sendOtp($email);
}
?>
<!-- Simple form -->
<form method="post" action="">
    <input type="email" name="email" placeholder="Enter your registered email" required>
    <button type="submit" name="btn-forgot">Send OTP</button>
</form>
