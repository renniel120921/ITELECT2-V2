<?php
session_start();
require_once __DIR__.'/../../../database/dbconnection.php';

class PasswordReset
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn =  $database->dbConnection();
    }

    public function verifyOtpAndResetPassword($email, $otp, $new_password)
    {
        // Check if OTP is valid and not expired
        $stmt = $this->conn->prepare("SELECT * FROM password_resets WHERE email = :email AND otp = :otp AND expires_at > NOW()");
        $stmt->execute([':email' => $email, ':otp' => $otp]);
        $resetData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$resetData) {
            echo "<script>alert('Invalid or expired OTP!'); window.location.href='reset-password.php?email=$email';</script>";
            exit;
        }

        // OTP valid, update password (use password_hash instead of md5 — md5 is insecure)
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $this->conn->prepare("UPDATE user SET password = :password WHERE email = :email");
        $stmt->execute([
            ':password' => $hashed_password,
            ':email' => $email
        ]);

        // Delete used OTP
        $stmt = $this->conn->prepare("DELETE FROM password_resets WHERE email = :email");
        $stmt->execute([':email' => $email]);

        echo "<script>alert('Password reset successful. Please login with your new password.'); window.location.href='login.php';</script>";
        exit;
    }
}

if (isset($_POST['btn-reset'])) {
    $email = trim($_POST['email']);
    $otp = trim($_POST['otp']);
    $new_password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($new_password !== $confirm_password) {
        echo "<script>alert('Password and Confirm Password do not match!'); window.location.href='reset-password.php?email=$email';</script>";
        exit;
    }

    $passwordReset = new PasswordReset();
    $passwordReset->verifyOtpAndResetPassword($email, $otp, $new_password);
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
    width: 350px;
  }

  input[type="hidden"] {
    display: none;
  }

  input[type="text"],
  input[type="password"] {
    width: 100%;
    padding: 12px 14px;
    margin-bottom: 18px;
    border: 1.5px solid #ccc;
    border-radius: 5px;
    font-size: 15px;
    transition: border-color 0.3s ease;
  }

  input[type="text"]:focus,
  input[type="password"]:focus {
    border-color: #007BFF;
    outline: none;
  }

  button[name="btn-reset"] {
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

  button[name="btn-reset"]:hover {
    background-color: #0056b3;
  }
</style>

<form method="post" action="">
    <input type="hidden" name="email" value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>" required>
    <input type="text" name="otp" placeholder="Enter OTP" required>
    <input type="password" name="password" placeholder="New Password" required>
    <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
    <button type="submit" name="btn-reset">Reset Password</button>
</form>
