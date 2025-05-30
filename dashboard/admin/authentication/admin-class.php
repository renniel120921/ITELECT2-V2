<?php
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
        session_start(); // make sure session is started here
        $this->settings = new SystemConfig();
        $this->smtp_email = $this->settings->getSmtpEmail();
        $this->smtp_password = $this->settings->getSmtpPassword();

        $database = new Database();
        $this->conn = $database->dbConnection();
    }

    // Send OTP for verification
    public function sendOtp($otp, $email)
    {
        if (empty($email)) {
            echo "<script>alert('No email found'); window.location.href = '../../../';</script>";
            exit();
        }

        // Check if email exists already
        $stmt = $this->runQuery("SELECT * FROM user WHERE email = :email");
        $stmt->execute([":email" => $email]);

        if ($stmt->rowCount() > 0) {
            echo "<script>alert('Email already taken. Please try another one.'); window.location.href = '../../../';</script>";
            exit();
        }

        $_SESSION["OTP"] = $otp;

        $subject = "OTP Verification";
        $message = $this->generateOtpEmailBody($email, $otp);

        $this->send_email($email, $message, $subject, $this->smtp_email, $this->smtp_password);

        echo "<script>alert('We sent the OTP to $email!'); window.location.href='../../../verify-otp.php';</script>";
    }

    private function generateOtpEmailBody($email, $otp)
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
                .container{
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
                <p>Hello, $email</p>
                <p>Your OTP is: <strong>$otp</strong></p>
                <p>If you didn't request an OTP, please ignore this email.</p>
                <p>Thank you!</p>
            </div>
        </body>
        </html>
        ";
    }

    // Verify OTP and add admin user
    public function verifyOtp($username, $email, $password, $tokencode, $otp, $csrf_token)
    {
        if (empty($otp) || !isset($_SESSION["OTP"])) {
            echo "<script>alert('No OTP Found!'); window.location.href='../../../';</script>";
            exit;
        }

        if ($otp !== $_SESSION["OTP"]) {
            echo "<script>alert('Invalid OTP!'); window.location.href='../../../verify-otp.php';</script>";
            exit;
        }

        // OTP is valid, unset session OTP
        unset($_SESSION["OTP"]);

        // Add admin user now
        $this->addAdmin($csrf_token, $username, $email, $password);

        $subject = "Verification Success";
        $message = $this->generateWelcomeEmailBody($email);

        $this->send_email($email, $message, $subject, $this->smtp_email, $this->smtp_password);

        echo "<script>alert('Thank you! Your account is verified.'); window.location.href='../../../';</script>";

        // Clear stored session form data after successful verification
        unset($_SESSION["not_verify_username"], $_SESSION["not_verify_email"], $_SESSION["not_verify_password"]);
    }

    private function generateWelcomeEmailBody($email)
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
                .container{
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
                <p>Hello, <strong>$email</strong></p>
                <p>Welcome to Mikko System</p>
                <p>If you did not sign up for an account, please ignore this email.</p>
                <p>Thank you!</p>
            </div>
        </body>
        </html>
        ";
    }

    // Add admin user
    public function addAdmin($csrf_token, $username, $email, $password)
    {
        // Check email exists
        $stmt = $this->runQuery("SELECT * FROM user WHERE email = :email");
        $stmt->execute([":email" => $email]);
        if ($stmt->rowCount() > 0) {
            echo "<script>alert('Email already exists!'); window.location.href='../../../';</script>";
            exit;
        }

        // CSRF validation
        if (!isset($csrf_token) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
            echo "<script>alert('Invalid CSRF Token!'); window.location.href='../../../';</script>";
            exit;
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
            echo "<script>alert('Admin Added Successfully!'); window.location.href='../../../';</script>";
            exit;
        } else {
            echo "<script>alert('Error Adding Admin!'); window.location.href='../../../';</script>";
            exit;
        }
    }

    // Admin sign-in using email and password
    public function adminSignin($email, $password, $csrf_token)
    {
        try {
            // CSRF check
            if (!isset($csrf_token) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
                echo "<script>alert('Invalid CSRF Token!'); window.location.href='../../../';</script>";
                exit;
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

                echo "<script>alert('Welcome!'); window.location.href='../';</script>";
                exit;
            } else {
                echo "<script>alert('Invalid Credentials!'); window.location.href='../../../';</script>";
                exit;
            }
        } catch (PDOException $ex) {
            echo $ex->getMessage();
        }
    }

    // Logging user activity
    public function logs($activity, $user_id)
    {
        $stmt = $this->runQuery("INSERT INTO logs (activity, user_id) VALUES (:activity, :user_id)");
        $stmt->execute([
            ":activity" => $activity,
            ":user_id" => $user_id
        ]);
    }

    // Run PDO query with error handling
    public function runQuery($sql)
    {
        try {
            $stmt = $this->conn->prepare($sql);
            return $stmt;
        } catch (PDOException $e) {
            die("Query error: " . $e->getMessage());
        }
    }

    // Send email using PHPMailer
    public function send_email($to, $message, $subject, $from, $password)
    {
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $from;
            $mail->Password = $password;
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom($from, 'Mikko System');
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;

            // Add embedded logo image if you have one
            // $mail->addEmbeddedImage('/path/to/logo.png', 'logo');

            $mail->send();
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }
}
