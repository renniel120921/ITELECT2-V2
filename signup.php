<?php
session_start();
require 'config/db.php';
require 'config/email.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Basic validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = "Email already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $otp = rand(100000, 999999);
            $otp_expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

            // Insert user with status=0 (not verified)
            $stmt = $pdo->prepare("INSERT INTO user (username, email, password, status, otp, otp_expiry, created_at) VALUES (?, ?, ?, 0, ?, ?, NOW())");
            $inserted = $stmt->execute([$username, $email, $hashed_password, $otp, $otp_expiry]);

            if ($inserted) {
                // Send OTP email
                if (sendOTPEmail($email, $otp, $pdo)) {
                    $_SESSION['email_for_otp'] = $email;
                    header("Location: verify_otp.php");
                    exit;
                } else {
                    $error = "Failed to send OTP email.";
                }
            } else {
                $error = "Failed to register user.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Sign Up</title></head>
<body>
<h2>Sign Up</h2>
<?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="post" action="">
    Username: <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"><br><br>
    Email: <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"><br><br>
    Password: <input type="password" name="password"><br><br>
    <button type="submit">Sign Up</button>
</form>
</body>
</html>
