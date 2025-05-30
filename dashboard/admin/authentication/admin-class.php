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
        session_start();
        $this->settings = new SystemConfig();
        $this->smtp_email = $this->settings->getSmtpEmail();
        $this->smtp_password = $this->settings->getSmtpPassword();

        $database = new Database();
        $this->conn = $database->dbConnection();
    }

    public function sendOtp($otp, $email)
    {
        if (empty($email)) {
            echo "<script>alert('No email found'); window.location.href = '../../../';</script>";
            exit();
        }

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
                body { font-family: Arial, sans-serif; background-color: #f5f5f5; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 30px; background-color: #fff; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                h1 { color: #333; font-size: 24px; margin-bottom: 20px; }
                p { color: #666; font-size: 16px; margin-bottom: 10px; }
                .logo { text-align: center; margin-bottom: 30px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='logo'><img src='cid:logo' alt='logo' width='150'></div>
                <h1>OTP VERIFICATION</h1>
                <p>Hello, $email</p>
                <p>Your OTP is: <strong>$otp</strong></p>
                <p>If you didn't request an OTP, please ignore this email.</p>
                <p>Thank you!</p>
            </div>
        </body>
        </html>";
    }

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

        unset($_SESSION["OTP"]);

        $this->addAdmin($csrf_token, $username, $email, $password);

        $subject = "Verification Success";
        $message = $this->generateWelcomeEmailBody($email);
        $this->send_email($email, $message, $subject, $this->smtp_email, $this->smtp_password);

        echo "<script>alert('Thank you! Your account is verified.'); window.location.href='../../../';</script>";

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
                body { font-family: Arial, sans-serif; background-color: #f5f5f5; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 30px; background-color: #fff; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                h1 { color: #333; font-size: 24px; margin-bottom: 20px; }
                p { color: #666; font-size: 16px; margin-bottom: 10px; }
                .logo { text-align: center; margin-bottom: 30px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='logo'><img src='cid:logo' alt='logo' width='150'></div>
                <h1>Welcome</h1>
                <p>Hello, <strong>$email</strong></p>
                <p>Welcome to Mikko System</p>
                <p>If you did not sign up for an account, please ignore this email.</p>
                <p>Thank you!</p>
            </div>
        </body>
        </html>";
    }

    public function addAdmin($csrf_token, $username, $email, $password)
    {
        $stmt = $this->runQuery("SELECT * FROM user WHERE email = :email");
        $stmt->execute([":email" => $email]);
        if ($stmt->rowCount() > 0) {
            echo "<script>alert('Email already exists!'); window.location.href='../../../';</script>";
            exit;
        }

        if (!isset($csrf_token) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
            echo "<script>alert('Invalid CSRF Token!'); window.location.href='../../../';</script>";
            exit;
        }

        unset($_SESSION['csrf_token']);

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

    public function adminSignin($email, $password, $csrf_token)
    {
        try {
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

    public function logs($activity, $user_id)
    {
        $stmt = $this->runQuery("INSERT INTO logs (activity, user_id) VALUES (:activity, :user_id)");
        $stmt->execute([
            ":activity" => $activity,
            ":user_id" => $user_id
        ]);
    }

    public function runQuery($sql)
    {
        try {
            return $this->conn->prepare($sql);
        } catch (PDOException $e) {
            die("Query error: " . $e->getMessage());
        }
    }

    public function send_email($to, $message, $subject, $from, $password)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $from;
            $mail->Password = $password;
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom($from, 'Mikko System');
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;

            // Attach logo as inline image if available
            $logoPath = __DIR__ . '/../../../assets/img/logo.png';
            if (file_exists($logoPath)) {
                $mail->addEmbeddedImage($logoPath, 'logo', 'logo.png');
            }

            $mail->send();
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
        }
    }
}
?>
