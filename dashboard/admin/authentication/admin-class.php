<?php
require_once __DIR__ . '/../../../database/dbconnection.php';
include_once __DIR__ . '/../../../config/settings-configuration.php';
require_once __DIR__ . "/../../../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
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

    public function sendOtp($otp, $email)
    {
        if ($email == null) {
            echo "<script>alert('No email found'); window.location.href = '../../../';</script>";
            exit();
        } else {
            $stmt = $this->runQuery("SELECT * FROM user WHERE email = :email");
            $stmt->execute([":email" => $email]);
            $stmt->fetch(PDO::FETCH_ASSOC);

            if ($stmt->rowCount() > 0) {
                echo "<script>alert('Email already taken. Please try another one.'); window.location.href = '../../../';</script>";
                exit();
            } else {
                $_SESSION["OTP"] = $otp;
                $subject = "OTP Verification";
                $message = "
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

                        button {
                            display: inline-block;
                            padding: 12px 24px;
                            background-color: #0088cc;
                            color: #ffffff;
                            text-decoration: none;
                            border-radius: 4px;
                            font-size: 16px;
                            margin-top: 20px;
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
                        <p>Your OTP is: $otp</p>
                        <p>If you didn't request an OTP, please ignore this email.</p>
                        <p>Thank you!</p>
                    </div>
                </body>
                </html>
                ";
                $this->send_email($email, $message, $subject, $this->smtp_email, $this->smtp_password);
                echo "<script>alert('We sent the OTP to $email!'); window.location.href='../../../verify-otp.php';</script>";
            }
        }
    }

    public function verifyOtp($username, $email, $password, $token_code, $otp, $csrf_token)
    {
        if ($otp == $_SESSION["OTP"]) {
            unset($_SESSION["OTP"]);

            $this->addAdmin($csrf_token, $username, $email, $password);
            $subject = "Verification Success";
            $message = "
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

                    button {
                        display: inline-block;
                        padding: 12px 24px;
                        background-color: #0088cc;
                        color: #ffffff;
                        text-decoration: none;
                        border-radius: 4px;
                        font-size: 16px;
                        margin-top: 20px;
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
                    <p>Welcome to Renniel System</p>
                    <p>If you did not sign up for an account, you can safely ignore this email.</p>
                    <p>Thank you!</p>
                </div>
            </body>
            </html>
            ";

            $this->send_email($email, $message, $subject, $this->smtp_email, $this->smtp_password);
            echo "<script>alert('Thank you!'); window.location.href='../../../';</script>";

            unset($_SESSION["not_verify_username"]);
            unset($_SESSION["not_verify_email"]);
            unset($_SESSION["not_verify_password"]);
        } else if ($otp == null) {
            echo "<script>alert('No OTP Found!'); window.location.href='../../../';</script>";
            exit;
        } else {
            echo "<script>alert('It appears that the OTP you entered is invalid!'); window.location.href='../../../verify-otp.php';</script>";
            exit;
        }
    }

    public function addAdmin($csrf_token, $username, $email, $password)
    {
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
            echo "<script>alert('Invalid CSRF Token!'); window.location.href='../../../';</script>";
            exit;
        }
        unset($_SESSION['csrf_token']);

        $stmt = $this->runQuery("SELECT * FROM user WHERE email = :email");
        $stmt->execute([":email" => $email]);

        if ($stmt->rowCount() > 0) {
            echo "<script>alert('Email already exists!'); window.location.href='../../../';</script>";
            exit;
        }

        $hash_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->runQuery("INSERT INTO user (username, email, password) VALUES (:username, :email, :password)");
        $exec = $stmt->execute([
            ":username" => $username,
            ":email" => $email,
            ":password" => $hash_password
        ]);

        if ($exec) {
            echo "<script>alert('Admin added successfully!'); window.location.href='../../../';</script>";
        } else {
            echo "<script>alert('Error adding admin!'); window.location.href='../../../';</script>";
        }
    }

    public function adminSignin($email, $password, $csrf_token)
    {
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            if (!isset($csrf_token) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
                echo "<script>alert('Invalid CSRF Token!'); window.location.href='../../../';</script>";
                exit;
            }
            unset($_SESSION['csrf_token']);

            $email = trim($email);

            $stmt = $this->runQuery("SELECT * FROM user WHERE email = :email LIMIT 1");
            $stmt->execute([":email" => $email]);
            $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($userRow) {
                if (password_verify($password, $userRow['password'])) {
                    $activity = "Has Successfully signed in";
                    $user_id = $userRow['id'];
                    $this->logs($activity, $user_id);

                    $_SESSION['adminSession'] = $userRow['id'];
                    $_SESSION['email'] = $userRow['email'];
                    $_SESSION['username'] = $userRow['username'];
                    $_SESSION['loggedin'] = true;

                    header("Location: ../../dashboard/index.php");
                    exit;
                } else {
                    echo "<script>alert('Incorrect password.'); window.location.href='../../../';</script>";
                    exit;
                }
            } else {
                echo "<script>alert('Email not found.'); window.location.href='../../../';</script>";
                exit;
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function runQuery($sql)
    {
        return $this->conn->prepare($sql);
    }

    public function logs($activity, $user_id)
    {
        $stmt = $this->runQuery("INSERT INTO activity_log (activity, user_id) VALUES (:activity, :user_id)");
        $stmt->execute([
            ":activity" => $activity,
            ":user_id" => $user_id
        ]);
    }

    private function send_email($to, $message, $subject, $smtp_email, $smtp_password)
    {
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $smtp_email;
            $mail->Password = $smtp_password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            //Recipients
            $mail->setFrom($smtp_email, 'Mikko System');
            $mail->addAddress($to);

            //Attachments
            $mail->addEmbeddedImage(__DIR__ . '/../../../assets/images/logo.png', 'logo');

            //Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;

            $mail->send();
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            echo "<script>alert('Email sending failed.');</script>";
        }
    }
}
?>
