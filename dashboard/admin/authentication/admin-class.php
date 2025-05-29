<?php
session_start();

require_once __DIR__.'/../../../database/dbconnection.php';
include_once __DIR__.'/../../../config/settings-configuration.php';
require_once __DIR__."/../../../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ADMIN
{
    private $conn;
    private $settings;
    private $smtp_email;
    private $smtp_password;

    public function __construct()
    {
        $this->settings = new SystemConfig();
        $this->smtp_email = $this->settings->getSmtpEmail();
        $this->smtp_password = $this->settings->getSmtpPassword();

        $database = new Database();
        $this->conn =  $database->dbConnection();
    }

    public function sendOtp(int $otp, string $email): void
    {
        if (empty($email)) {
            $this->redirectWithAlert("No email found", '../../../');
        }

        // Check if email exists in DB to prevent duplicates (optional here or only in addAdmin)
        $stmt = $this->runQuery("SELECT 1 FROM user WHERE email = :email");
        $stmt->execute([":email" => $email]);
        if ($stmt->fetch()) {
            $this->redirectWithAlert("Email already taken. Please try another one.", '../../../');
        }

        $_SESSION["OTP"] = $otp;

        $subject = "OTP Verification";
        $message = $this->getOtpEmailHtml($email, $otp);

        $this->send_email($email, $message, $subject);
        $this->redirectWithAlert("We sent the OTP to $email!", '../../../verify-otp.php');
    }

    private function getOtpEmailHtml(string $email, int $otp): string
    {
        return "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>OTP VERIFICATION</title>
            <style>
                body {
                    font-family: Arial, Helvetica, sans-serif;
                    background-color: #f5f5f5;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 30px;
                    background-color: #ffffff;
                    border-radius: 4px;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                }
                h1 {
                    color: #333333;
                    font-size: 24px;
                    margin-bottom: 20px;
                }
                p {
                    color: #666666;
                    font-size: 16px;
                    margin-bottom: 10px;
                }
                .logo {
                    display: block;
                    text-align: center;
                    margin-bottom: 30px;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='logo'>
                    <img src='cid:logo' alt='logo' width='150'>
                </div>
                <h1>OTP VERIFICATION</h1>
                <p>Hello, {$email}</p>
                <p>Your OTP is: {$otp}</p>
                <p>If you didn't request an OTP, please ignore this email.</p>
                <p>Thank you!</p>
            </div>
        </body>
        </html>
        ";
    }

    public function verifyOtp(string $username, string $email, string $password, string $tokenCode, string $otp, string $csrf_token): void
    {
        if (empty($otp) || !isset($_SESSION["OTP"])) {
            $this->redirectWithAlert("No OTP Found!", '../../../');
        }

        if ($otp !== $_SESSION["OTP"]) {
            $this->redirectWithAlert("It appears that the OTP you entered is invalid!", '../../../verify-otp.php');
        }

        // OTP matches
        unset($_SESSION["OTP"]);

        $this->addAdmin($csrf_token, $username, $email, $password);

        $subject = "Verification Success";
        $message = $this->getVerificationSuccessHtml($email);

        $this->send_email($email, $message, $subject);

        // Clear temp session vars after successful verification
        unset($_SESSION["not_verify_username"], $_SESSION["not_verify_email"], $_SESSION["not_verify_password"]);

        $this->redirectWithAlert("Thank you!", '../../../');
    }

    private function getVerificationSuccessHtml(string $email): string
    {
        return "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Verification Success</title>
            <style>
                body {
                    font-family: Arial, Helvetica, sans-serif;
                    background-color: #f5f5f5;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 30px;
                    background-color: #ffffff;
                    border-radius: 4px;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                }
                h1 {
                    color: #333333;
                    font-size: 24px;
                    margin-bottom: 20px;
                }
                p {
                    color: #666666;
                    font-size: 16px;
                    margin-bottom: 10px;
                }
                .logo {
                    display: block;
                    text-align: center;
                    margin-bottom: 30px;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='logo'>
                    <img src='cid:logo' alt='logo' width='150'>
                </div>
                <h1>Welcome</h1>
                <p>Hello, <strong>{$email}</strong></p>
                <p>Welcome to Mikko System</p>
                <p>If you did not sign up for an account, you can safely ignore this email.</p>
                <p>Thank you!</p>
            </div>
        </body>
        </html>
        ";
    }

    public function addAdmin(string $csrf_token, string $username, string $email, string $password): void
    {
        $stmt = $this->runQuery("SELECT 1 FROM user WHERE email = :email");
        $stmt->execute([":email" => $email]);

        if ($stmt->fetch()) {
            $this->redirectWithAlert("Email already exists!", '../../../');
        }

        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
            $this->redirectWithAlert("Invalid CSRF Token!", '../../../');
        }

        unset($_SESSION['csrf_token']);

        // Use password_hash instead of md5
        $hash_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->runQuery("INSERT INTO user (username, email, password) VALUES (:username, :email, :password)");
        $exec = $stmt->execute([
            ":username" => $username,
            ":email" => $email,
            ":password" => $hash_password
        ]);

        if ($exec) {
            $this->redirectWithAlert("Admin Added Successfully!", '../../../');
        } else {
            $this->redirectWithAlert("Error Adding Admin!", '../../../');
        }
    }

   public function adminSignin(string $email, string $password, string $csrf_token): void
{
    try {
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
            $this->redirectWithAlert("Invalid CSRF Token!", '../../../');
        }

        unset($_SESSION['csrf_token']);

        $stmt = $this->runQuery("SELECT * FROM user WHERE email = :email");
        $stmt->execute([":email" => $email]);
        $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userRow && password_verify($password, $userRow['password'])) {
            $activity = "Has Successfully signed in";
            $user_id = $userRow['id'];
            $this->logs($activity, $user_id);

            $_SESSION['adminSession'] = $user_id;

            // Redirect to index.php — adjust path depending on your folder structure
            $this->redirectWithAlert("Welcome!", '../../../index.php');
        } else {
            $this->redirectWithAlert("Invalid Credentials!", '../../../');
        }
    } catch (PDOException $ex) {
        echo "Database error: " . $ex->getMessage();
        exit;
    }
}

    public function adminSignout(): void
    {
        session_unset();
        session_destroy();
        $this->redirectWithAlert("Sign Out Successfully!", '../../../');
    }

    private function send_email(string $email, string $message, string $subject): void
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->SMTPDebug = 0;
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtp_email;
            $mail->Password = $this->smtp_password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom($this->smtp_email, 'Mikko System');
            $mail->addAddress($email);
            $mail->addReplyTo($this->smtp_email);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;

            // Attach logo with cid 'logo' if needed
            // $mail->addEmbeddedImage('/path/to/logo.png', 'logo');

            $mail->send();
        } catch (Exception $e) {
            // Log or handle error as needed
            error_log("Mailer Error: " . $mail->ErrorInfo);
        }
    }

    private function runQuery(string $sql)
    {
        return $this->conn->prepare($sql);
    }

    private function logs(string $activity, int $user_id): void
    {
        $stmt = $this->conn->prepare("INSERT INTO logs (activity, user_id) VALUES (:activity, :user_id)");
        $stmt->execute([':activity' => $activity, ':user_id' => $user_id]);
    }

    private function redirectWithAlert(string $msg, string $location): void
    {
        echo "<script>
                alert('{$msg}');
                window.location.href = '{$location}';
              </script>";
        exit;
    }
}
