<?php
require_once __DIR__.'/../../../database/dbconnection.php';
include_once __DIR__.'/../../../config/settings-configuration.php';
require_once __DIR__."/../../../vendor/autoload.php";
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
        $this->conn =  $database->dbConnection();
    }

    public function sendOtp($otp, $email)
    {
        if($email == null) {
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
                $message = "..."; // message content omitted for brevity
                $this->send_email($email, $message, $subject, $this->smtp_email, $this->smtp_password);
                echo "<script>alert('We sent the OTP to $email!'); window.location.href='../../../verify-otp.php';</script>";
            }
        }
    }

    public function verifyOtp($username, $email, $password, $tokencode, $otp, $csrf_token)
    {
        if ($otp == $_SESSION["OTP"]) {
            unset($_SESSION["OTP"]);

            $this->addAdmin($csrf_token, $username, $email, $password);
            $subject = "Verification Success";
            $message = "..."; // message content omitted for brevity
            $this->send_email($email, $message, $subject, $this->smtp_email, $this->smtp_password);
            echo "<script>alert('Thank you!'); window.location.href='../../../';</script>";

            unset($_SESSION["not_verify_username"]);
            unset($_SESSION["not_verify_email"]);
            unset($_SESSION["not_verify_password"]);
        } else if($otp == null) {
            echo "<script>alert('No OTP Found!'); window.location.href='../../../';</script>";
            exit;
        } else {
            echo "<script>alert('It appears that the OTP you entered is invalid!'); window.location.href='../../../verify-otp.php';</script>";
            exit;
        }
    }

    public function addAdmin($csrf_token, $username, $email, $password)
    {
        $stmt = $this->runQuery("SELECT * FROM user WHERE email =:email");
        $stmt->execute(array(":email" => $email));

        if($stmt->rowCount() > 0){
            echo "<script>alert('Email already exists!'); window.location.href='../../../';</script>";
            exit;
        }

        if(!isset($csrf_token) || !hash_equals($_SESSION['csrf_token'], $csrf_token)){
            echo "<script>alert('Invalid CSRF Token!'); window.location.href='../../../';</script>";
            exit;
        }

        unset($_SESSION['csrf_token']);

        $hash_password = md5($password);

        $stmt = $this->runQuery("INSERT INTO user (username, email, password) VALUES (:username, :email, :password)");
        $exec = $stmt->execute(array(
            ":username" => $username,
            ":email" => $email,
            ":password" => $hash_password
        ));

        if($exec){
            echo "<script>alert('Admin Added Successfully!'); window.location.href='../../../';</script>";
            exit;
        } else {
            echo "<script>alert('Error Adding Admin!'); window.location.href='../../../';</script>";
            exit;
        }
    }

    public function adminSignin($email, $password, $csrf_token)
    {
        try{
            if(!isset($csrf_token) || !hash_equals($_SESSION['csrf_token'], $csrf_token)){
                echo "<script>alert('Invalid CSRF Token!'); window.location.href='../../../';</script>";
                exit;
            }
            unset($_SESSION['csrf_token']);

            $stmt = $this->runQuery("SELECT * FROM user WHERE email = :email");
            $stmt->execute(array(":email" => $email));
            $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

            if($stmt->rowCount() == 1){
                $activity = "Has Successfully signed in";
                $user_id = $userRow['id'];
                $this->logs($activity, $user_id);

                $_SESSION['adminSession'] = $user_id;

                echo "<script>alert('Welcome!'); window.location.href='../';</script>";
                exit;
            }else{
                echo "<script>alert('Invalid Credentials!'); window.location.href='../../../';</script>";
                exit;
            }

        }catch(PDOException $ex){
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
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = "tls";
        $mail->Host = "smtp.gmail.com";
        $mail->Port = "587";
        $mail->addAddress($email);
        $mail->Username = $smtp_email;
        $mail->Password = $smtp_password;
        $mail->setFrom($smtp_email, "Renniel");
        $mail->Subject = $subject;
        $mail->msgHTML($message);
        $mail->Send();
    }

    public function logs($activity, $user_id)
    {
        $stmt = $this->conn->prepare("INSERT INTO logs (user_id, activity) VALUES (:user_id, :activity)");
        $stmt->execute([":user_id" => $user_id, ":activity" => $activity]);
    }

    public function runQuery($sql)
    {
        return $this->conn->prepare($sql);
    }
}
?>
