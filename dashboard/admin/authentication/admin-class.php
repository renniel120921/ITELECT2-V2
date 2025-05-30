<?php
require_once __DIR__ . '/../../../config/settings-configuration.php';
require_once __DIR__ . '/../../../database/dbconnection.php';
require_once 'vendor/autoload.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Admin
{
    private $conn;

    public function __construct()
    {
        global $config;
        $database = new Database($config);
        $this->conn = $database->dbConnection();

        // ✅ Set MySQL timezone to match PHP
        $this->conn->exec("SET time_zone = 'Asia/Manila'");
    }

    // 📩 Register user with OTP verification
    public function register($username, $email, $password)
    {
        $otp = rand(100000, 999999);

        // Use timezone-aware DateTime for OTP expiry
        $otp_expiry_time = new DateTime('now', new DateTimeZone('Asia/Manila'));
        $otp_expiry_time->modify('+5 minutes');
        $otp_expiry = $otp_expiry_time->format('Y-m-d H:i:s');

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(16));

        $query = "INSERT INTO user (username, email, password, status, tokencode, created_at, otp, otp_expiry)
                  VALUES (:username, :email, :password, 'inactive', :token, NOW(), :otp, :otp_expiry)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':otp', $otp);
        $stmt->bindParam(':otp_expiry', $otp_expiry);

        if ($stmt->execute()) {
            $user_id = $this->conn->lastInsertId();
            $emailSent = $this->sendOtpEmail($email, $otp);
            $this->logActivity($user_id, "OTP sent for registration" . ($emailSent ? "" : " (FAILED)"));

            // 🔍 Debug inserted time values
            $check = $this->conn->prepare("SELECT created_at, otp_expiry FROM user WHERE id = :id");
            $check->bindParam(':id', $user_id);
            $check->execute();
            $user = $check->fetch(PDO::FETCH_ASSOC);

            error_log("PHP OTP Expiry: " . $otp_expiry);
            error_log("MySQL created_at: " . $user['created_at']);
            error_log("MySQL otp_expiry: " . $user['otp_expiry']);

            return true;
        }

        return false;
    }

    // ✅ Verify OTP and activate user
    public function verifyOtp($email, $enteredOtp)
    {
        $query = "SELECT * FROM user WHERE email = :email AND status = 'inactive'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $current_time = (new DateTime('now', new DateTimeZone('Asia/Manila')))->format('Y-m-d H:i:s');

            // 🐛 Debug logs
            error_log("Entered OTP: $enteredOtp");
            error_log("Stored OTP: " . $user['otp']);
            error_log("OTP Expiry: " . $user['otp_expiry']);
            error_log("Current Time: $current_time");

            if ($user['otp'] == $enteredOtp && $current_time <= $user['otp_expiry']) {
                $update = "UPDATE user SET status = 'active', otp = NULL, otp_expiry = NULL WHERE email = :email";
                $stmtUpdate = $this->conn->prepare($update);
                $stmtUpdate->bindParam(':email', $email);
                $stmtUpdate->execute();

                $this->logActivity($user['id'], "OTP verified and account activated");
                return true;
            }
        } else {
            error_log("No inactive user found with email: $email");
        }

        error_log("OTP verification failed for email: $email with OTP: $enteredOtp");
        return false;
    }

    // ✉️ Send OTP email
    private function sendOtpEmail($email, $otp)
    {
        $mail = new PHPMailer(true);

        try {
            // SMTP config
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'rennielsalazar948@gmail.com';

            // 🔐 Load secure SMTP password
            $smtpPassword = getenv('SMTP_PASSWORD');
            if (!$smtpPassword || $smtpPassword === 'SMTP_PASSWORD') {
                $smtpPassword = 'rfel kxiz jhip nobw'; // fallback for testing
            }

            $mail->Password = $smtpPassword;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('rennielsalazar948@gmail.com', 'Welcome Client');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Your OTP Code';
            $mail->Body    = "Your OTP code is: <strong>$otp</strong>. It will expire in 5 minutes.";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }

    // 📝 Log activity
    private function logActivity($user_id, $activity)
    {
        $query = "INSERT INTO logs (user_id, activity, created_at)
                  VALUES (:user_id, :activity, NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':activity', $activity);
        $stmt->execute();
    }
}
