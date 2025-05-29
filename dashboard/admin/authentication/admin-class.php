<?php
session_start();

require_once __DIR__.'/../../../database/dbconnection.php';
include_once __DIR__.'/../../../config/settings-configuration.php';
require_once __DIR__."/../../../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Admin
{
    private $conn;
    private $settings;
    private $smtpEmail;
    private $smtpPassword;

    public function __construct()
    {
        $this->settings = new SystemConfig();
        $this->smtpEmail = $this->settings->getSmtpEmail();
        $this->smtpPassword = $this->settings->getSmtpPassword();

        $database = new Database();
        $this->conn = $database->dbConnection();
    }

    public function sendOtp($otp, $email)
    {
        if (empty($email)) {
            $this->redirectWithAlert("No email found", '../../../');
        }

        // Check if email already exists in user table
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->rowCount() > 0) {
            $this->redirectWithAlert("Email already taken. Please try another one.", '../../../');
        }

        $_SESSION["OTP"] = $otp;
        $_SESSION["otp_email"] = $email;

        $subject = "OTP Verification";
        $message = $this->getOtpEmailTemplate($email, $otp);

        if ($this->sendEmail($email, $message, $subject)) {
            $this->redirectWithAlert("We sent the OTP to $email!", '../../../verify-otp.php');
        } else {
            $this->redirectWithAlert("Failed to send OTP email. Please try again.", '../../../');
        }
    }

    public function verifyOtp($username, $email, $password, $csrfToken, $otp)
    {
        if (empty($_SESSION["OTP"]) || empty($_SESSION["otp_email"])) {
            $this->redirectWithAlert("No OTP Found! Please request again.", '../../../');
        }

        if ($otp !== $_SESSION["OTP"] || $email !== $_SESSION["otp_email"]) {
            $this->redirectWithAlert("Invalid OTP entered!", '../../../verify-otp.php');
        }

        // OTP is valid, proceed
        unset($_SESSION["OTP"], $_SESSION["otp_email"]);

        $this->addAdmin($csrfToken, $username, $email, $password);
    }

    public function addAdmin($csrfToken, $username, $email, $password)
    {
        if (!isset($csrfToken) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            $this->redirectWithAlert("Invalid CSRF Token!", '../../../');
        }

        // Clear token after use
        unset($_SESSION['csrf_token']);

        // Check if email already exists
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->rowCount() > 0) {
            $this->redirectWithAlert("Email already exists!", '../../../');
        }

        // Hash password securely
        $hashPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->conn->prepare("INSERT INTO user (username, email, password) VALUES (:username, :email, :password)");
        $success = $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $hashPassword
        ]);

        if ($success) {
            $this->redirectWithAlert("Admin Added Successfully!", '../../../');
        } else {
            $this->redirectWithAlert("Error Adding Admin!", '../../../');
        }
    }

    public function adminSignin($email, $password, $csrfToken)
    {
        if (!isset($csrfToken) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            $this->redirectWithAlert("Invalid CSRF Token!", '../../../');
        }
        unset($_SESSION['csrf_token']);

        $email = trim($email);
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userRow && password_verify($password, $userRow['password'])) {
            $userId = $userRow['id'];
            $this->logActivity("Has Successfully signed in", $userId);
            $_SESSION['adminSession'] = $userId;

            $this->redirectWithAlert("Welcome!", '../');
        } else {
            $this->redirectWithAlert("Invalid Credentials!", '../../../');
        }
    }

    public function adminSignout()
    {
        session_unset();
        session_destroy();
        header('Location: ../../../');
        exit;
    }

    private function sendEmail($email, $message, $subject): bool
    {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->SMTPDebug = 0;
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = "tls";
            $mail->Host = "smtp.gmail.com";
            $mail->Port = 587;

            $mail->Username = $this->smtpEmail;
            $mail->Password = $this->smtpPassword;
            $mail->setFrom($this->smtpEmail, "Renniel");
            $mail->addAddress($email);

            $mail->Subject = $subject;
            $mail->msgHTML($message);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mail Error: " . $mail->ErrorInfo);
            return false;
        }
    }

    private function logActivity($activity, $userId)
    {
        $stmt = $this->conn->prepare("INSERT INTO logs (user_id, activity) VALUES (:user_id, :activity)");
        $stmt->execute([
            ':user_id' => $userId,
            ':activity' => $activity
        ]);
    }

    private function redirectWithAlert($message, $url)
    {
        echo "<script>alert('$message'); window.location.href='$url';</script>";
        exit;
    }

    private function getOtpEmailTemplate($email, $otp): string
    {
        return "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>OTP VERIFICATION</title>
            <style>
                body {font-family: Arial, Helvetica, sans-serif; background-color: #f5f5f5; margin: 0; padding: 0;}
                .container{max-width:600px;margin:0 auto;padding:30px;background:#fff;border-radius:4px;box-shadow:0 2px 4px rgba(0,0,0,0.1);}
                h1 {color:#333; font-size:24px; margin-bottom:20px;}
                p {color:#666; font-size:16px; margin-bottom:10px;}
                .logo {display:block; text-align:center; margin-bottom:30px;}
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='logo'>
                    <img src='cid:logo' alt='logo' width='150'>
                </div>
                <h1>OTP VERIFICATION</h1>
                <p>Hello, $email</p>
                <p>Your OTP is: <strong>$otp</strong></p>
                <p>If you didn't request this, please ignore this email.</p>
                <p>Thank you!</p>
            </div>
        </body>
        </html>
        ";
    }
}

// Handler logic for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['btn-signup'])) {
        $_SESSION["not_verify_username"] = trim($_POST['username']);
        $_SESSION["not_verify_email"] = trim($_POST['email']);
        $_SESSION["not_verify_password"] = trim($_POST['password']);

        $email = $_SESSION["not_verify_email"];
        $otp = rand(100000, 999999);

        $admin = new Admin();
        $admin->sendOtp($otp, $email);
    }

    if (isset($_POST['btn-verify'])) {
        $otp = trim($_POST['otp']);
        $username = $_SESSION["not_verify_username"] ?? null;
        $email = $_SESSION["not_verify_email"] ?? null;
        $password = $_SESSION["not_verify_password"] ?? null;
        $csrfToken = $_POST['csrf_token'] ?? '';

        if (!$username || !$email || !$password) {
            (new Admin())->redirectWithAlert("Session expired. Please start again.", '../../../');
        }

        $admin = new Admin();
        $admin->verifyOtp($username, $email, $password, $csrfToken, $otp);
    }

    if (isset($_POST['btn-signin'])) {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $csrfToken = $_POST['csrf_token'] ?? '';

        $admin = new Admin();
        $admin->adminSignin($email, $password, $csrfToken);
    }

    if (isset($_POST['btn-logout'])) {
        (new Admin())->adminSignout();
    }
}
