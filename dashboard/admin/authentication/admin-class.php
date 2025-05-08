<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../database/dbconnection.php';
include_once __DIR__ . '/../../../config/settings-configuration.php';
require_once __DIR__ . "/../../../src/vendor/autoload.php";

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
        $this->conn = $database->dbConnection();
    }

    public function runQuery($sql)
    {
        return $this->conn->prepare($sql);
    }

    public function sendOtp($otp, $email)
    {
        if (!$email) {
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
        $message = <<<HTML
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial; background: #f5f5f5; }
                .container { max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 4px; }
                .logo { text-align: center; margin-bottom: 30px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='logo'><img src='cid:logo' alt='logo' width='150'></div>
                <h1>OTP VERIFICATION</h1>
                <p>Hello, $email</p>
                <p>Your OTP is: <strong>$otp</strong></p>
                <p>If you didn’t request this, please ignore it.</p>
            </div>
        </body>
        </html>
        HTML;

        $this->send_email($email, $message, $subject, $this->smtp_email, $this->smtp_password);
        echo "<script>alert('We sent the OTP to $email!'); window.location.href='../../../verify-otp.php';</script>";
    }

    public function verifyOtp($username, $email, $password, $tokencode, $otp, $csrf_token)
    {
        if (empty($otp)) {
            echo "<script>alert('No OTP Found!'); window.location.href='../../../';</script>";
            exit;
        }

        if ($otp === $_SESSION["OTP"]) {
            unset($_SESSION["OTP"]);

            $this->addAdmin($csrf_token, $username, $email, $password);

            $subject = "Verification Success";
            $message = <<<HTML
            <html><body>
                <div style='font-family: Arial; max-width: 600px; margin:auto; padding: 30px; background: white;'>
                    <img src='cid:logo' width='150'>
                    <h1>Welcome</h1>
                    <p>Hello, <strong>$email</strong></p>
                    <p>Welcome to Renniel  System</p>
                    <p>If you did not sign up, please ignore this email.</p>
                </div>
            </body></html>
            HTML;

            $this->send_email($email, $message, $subject, $this->smtp_email, $this->smtp_password);
            echo "<script>alert('Thank you!'); window.location.href='../../../';</script>";

            unset($_SESSION["not_verify_username"], $_SESSION["not_verify_email"], $_SESSION["not_verify_password"]);
        } else {
            echo "<script>alert('It appears that the OTP you entered is invalid!'); window.location.href='../../../verify-otp.php';</script>";
            exit;
        }
    }

    public function addAdmin($csrf_token, $username, $email, $password)
    {
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE email = :email");
        $stmt->execute([":email" => $email]);

        if ($stmt->rowCount() > 0) {
            echo "<script>alert('Email already exists!'); window.location.href='../../../';</script>";
            exit;
        }

        if (!isset($csrf_token) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
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
            if (!isset($csrf_token) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
                echo "<script>alert('Invalid CSRF Token!'); window.location.href='../../../';</script>";
                exit;
            }

            unset($_SESSION['csrf_token']);

            $stmt = $this->conn->prepare("SELECT * FROM user WHERE email = :email");
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

    public function adminSignout()
    {
        unset($_SESSION['adminSession']);
        echo "<script>alert('Sign Out Successfully!'); window.location.href='../../../';</script>";
        exit;
    }

    public function send_email($email, $message, $subject, $smtp_email, $smtp_password)
    {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;
            $mail->Username = $smtp_email;
            $mail->Password = $smtp_password;
            $mail->SMTPSecure = "tls";
            $mail->Port = 587;

            $mail->setFrom($smtp_email, "Renniel");
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;

            $mail->AddEmbeddedImage(__DIR__ . '/../../../assets/img/logo.png', 'logo'); // Update this path

            $mail->send();
        } catch (Exception $e) {
            echo "<script>alert('Email sending failed: {$mail->ErrorInfo}'); window.location.href='../../../';</script>";
            exit;
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
}
