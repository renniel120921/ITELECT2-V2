<?php
session_start();
require_once 'database/dbconnection.php';

// PHPMailer imports (adjust path if necessary)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'vendor/autoload.php';

$config = [
    'host'     => 'localhost',
    'port'     => '3306',
    'dbname'   => 'itelect2',
    'username' => 'root',
    'password' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!$email) {
        $error = "Email is required.";
    } else {
        try {
            $db = (new Database($config))->dbConnection();

            $stmt = $db->prepare("SELECT id FROM user WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                $user_id = $user['id'];
                $token = bin2hex(random_bytes(32));
                $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $stmt = $db->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $token, $expires_at]);

                $reset_link = "http://localhost/ITELECT2-V2/reset-password.php?token=" . $token;

                $to = $email;
                $subject = "Password Reset Request";
                $message = "Hi,\n\nClick the link below to reset your password:\n\n$reset_link\n\nThis link expires in 1 hour.\n\nIf you didn't request this, just ignore this email.";

                $mail = new PHPMailer(true);

                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'rennielsalazar948@gmail.com';  // Your Gmail address
                    $mail->Password   = 'rfel kxiz jhip nobw';    // Your Gmail app password or account password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    $mail->setFrom('no-reply@yourdomain.com', 'Reset Password ka ya??');
                    $mail->addAddress($to);

                    $mail->isHTML(false);
                    $mail->Subject = $subject;
                    $mail->Body    = $message;

                    $mail->send();
                    $success = "If this email exists in our system, you will receive a reset link shortly.";
                } catch (Exception $e) {
                    $error = "Failed to send reset email. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                // Uniform message regardless of user existence
                $success = "If this email exists in our system, you will receive a reset link shortly.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Forgot Password</title>
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
            width: 400px;
            text-align: center;
        }
        h2 {
            margin-bottom: 25px;
            color: #00ff90;
            font-weight: 700;
        }
        input[type="email"] {
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
        .error {
            background-color: #ff3b3b;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .success {
            background-color: #00cc6a;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        a {
            color: #00ff90;
            text-decoration: none;
            font-weight: 600;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Forgot Password</h2>

    <?php if (!empty($error)): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php elseif (!empty($success)): ?>
        <p class="success"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
        <input type="email" name="email" placeholder="Enter your email" required />
        <button type="submit">Send Reset Link</button>
    </form>
    <p><a href="login.php">Back to Login</a></p>
</div>
</body>
</html>
