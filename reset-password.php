<?php
session_start();
require_once 'database/dbconnection.php';

class PasswordReset
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->dbConnection();
    }

    public function verifyOtpAndResetPassword($email, $otp, $new_password)
    {
        $email = trim($email);
        $otp = trim($otp);

        // Step 1: Check if OTP exists and matches
        $stmt = $this->conn->prepare("SELECT * FROM password_resets WHERE email = :email AND otp = :otp");
        $stmt->execute([':email' => $email, ':otp' => $otp]);
        $resetData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$resetData) {
            $this->redirectWithError("Invalid OTP or Email!", $email);
        }

        // Step 2: Check OTP expiry
        if ($resetData['expires_at'] <= date('Y-m-d H:i:s')) {
            $this->redirectWithError("OTP has expired! Please request a new one.", $email);
        }

        // Step 3: Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Step 4: Update password in user table
        $stmt = $this->conn->prepare("UPDATE user SET password = :password WHERE email = :email");
        $stmt->execute([':password' => $hashed_password, ':email' => $email]);

        // Step 5: Delete used OTP
        $stmt = $this->conn->prepare("DELETE FROM password_resets WHERE email = :email");
        $stmt->execute([':email' => $email]);

        // Success message and redirect to login
        $_SESSION['success'] = "Password reset successful. Please login with your new password.";
        header("Location: login.php");
        exit;
    }

    private function redirectWithError($message, $email)
    {
        $_SESSION['error'] = $message;
        header("Location: reset-password.php?email=" . urlencode($email));
        exit;
    }
}

// Handle form submit
if (isset($_POST['btn-reset'])) {
    $email = trim($_POST['email']);
    $otp = trim($_POST['otp']);
    $new_password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Password and Confirm Password do not match!";
        header("Location: reset-password.php?email=" . urlencode($email));
        exit;
    }

    $passwordReset = new PasswordReset();
    $passwordReset->verifyOtpAndResetPassword($email, $otp, $new_password);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Reset Password</title>
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
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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

        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            font-weight: 600;
            text-align: center;
        }

        .error {
            background-color: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }

        .success {
            background-color: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }
    </style>
</head>

<body>

    <form method="post" action="">
        <?php if (!empty($_SESSION['error'])) : ?>
            <div class="message error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['success'])) : ?>
            <div class="message success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <input type="hidden" name="email" value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>" required>
        <input type="text" name="otp" placeholder="Enter OTP" required>
        <input type="password" name="password" placeholder="New Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
        <button type="submit" name="btn-reset">Reset Password</button>
    </form>

</body>

</html>

