<?php
session_start();

$conn = new mysqli('localhost', 'root', '', 'itelect2');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Load PHPMailer
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $username = trim($_POST['username'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($username) || strlen($username) < 3) {
        $error = "Username must be at least 3 characters.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $otp = rand(100000, 999999);

            $insert_stmt = $conn->prepare("INSERT INTO user (username, email, password, otp, otp_verified) VALUES (?, ?, ?, ?, 0)");
            $insert_stmt->bind_param("sssi", $username, $email, $hashed_password, $otp);

            if ($insert_stmt->execute()) {
                // Send OTP email
                $mail = new PHPMailer(true);

                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'rennielsalazar948@gmail.com'; // Your Gmail
                    $mail->Password   = 'rfel kxiz jhip nobw'; // App Password, keep this secure!
                    $mail->SMTPSecure = 'tls';
                    $mail->Port       = 587;

                    $mail->setFrom('rennielsalazar948@gmail.com', 'ITELECT2');
                    $mail->addAddress($email);
                    $mail->isHTML(false);
                    $mail->Subject = "Your OTP Code";
                    $mail->Body    = "Your OTP code is: $otp";

                    $mail->send();

                    $_SESSION['email'] = $email;
                    header("Location: verify-otp.php");
                    exit;
                } catch (Exception $e) {
                    $error = "Failed to send OTP email. Mailer Error: " . $mail->ErrorInfo;
                }
            } else {
                $error = "Database error: " . $insert_stmt->error;
            }
            $insert_stmt->close();
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Sign Up</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #121212;
        color: #eee;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }
    .container {
        background: #1f1f1f;
        padding: 30px 40px;
        border-radius: 8px;
        box-shadow: 0 0 15px #00ff90;
        width: 350px;
        text-align: center;
    }
    h2 {
        margin-bottom: 25px;
        color: #00ff90;
        font-weight: 700;
    }
    input[type="text"],
    input[type="email"],
    input[type="password"] {
        width: 100%;
        padding: 12px;
        margin: 10px 0 20px 0;
        border: none;
        border-radius: 4px;
        background: #2c2c2c;
        color: #eee;
        font-size: 14px;
    }
    input::placeholder {
        color: #888;
    }
    button {
        background-color: #00ff90;
        border: none;
        color: #121212;
        padding: 12px 0;
        width: 100%;
        border-radius: 4px;
        font-weight: 700;
        cursor: pointer;
        font-size: 16px;
        transition: background-color 0.3s ease;
    }
    button:hover {
        background-color: #00cc6a;
    }
    p {
        margin-top: 20px;
        font-size: 14px;
    }
    a {
        color: #00ff90;
        text-decoration: none;
        font-weight: 600;
    }
    a:hover {
        text-decoration: underline;
    }
    .error {
        background-color: #ff3b3b;
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 15px;
        font-weight: 600;
    }
</style>
</head>
<body>
<div class="container">
    <h2>Create Account</h2>
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="POST" autocomplete="off" novalidate>
        <input type="text" name="username" placeholder="Username" required minlength="3" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" />
        <input type="email" name="email" placeholder="Email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
        <input type="password" name="password" placeholder="Password (min 6 chars)" required minlength="6" />
        <button type="submit">Sign Up</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
</div>
</body>
</html>
