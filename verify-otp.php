<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'itelect2');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";
$success = "";

// Redirect if email session is missing
if (!isset($_SESSION['email'])) {
    header("Location: signup.php");
    exit;
}

$email = $_SESSION['email'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_otp = trim($_POST['otp']);

    if (!ctype_digit($entered_otp) || strlen($entered_otp) !== 6) {
        $error = "OTP must be a 6-digit number.";
    } else {
        // Fetch OTP from database
        $stmt = $conn->prepare("SELECT otp FROM user WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($stored_otp);
        $stmt->fetch();
        $stmt->close();

        if ($entered_otp === $stored_otp) {
            // Update verification status
            $update_stmt = $conn->prepare("UPDATE user SET otp_verified = 1 WHERE email = ?");
            $update_stmt->bind_param("s", $email);
            if ($update_stmt->execute()) {
                $success = "✅ OTP verified successfully. You may now <a href='login.php'>log in</a>.";
                session_destroy(); // clear session only after successful update
            } else {
                $error = "Database update failed. Please try again.";
            }
            $update_stmt->close();
        } else {
            $error = "❌ Incorrect OTP. Please double-check your email.";
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP</title>
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
            color: #00ff90;
            margin-bottom: 20px;
        }
        input[type="text"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0 20px 0;
            border: none;
            border-radius: 4px;
            background: #2c2c2c;
            color: #eee;
            font-size: 14px;
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
        .error, .success {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 4px;
            font-weight: 600;
        }
        .error { background-color: #ff3b3b; }
        .success { background-color: #00cc6a; }
        .success a {
            color: #121212;
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Verify OTP</h2>
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php else: ?>
        <form method="POST">
            <input type="text" name="otp" placeholder="Enter 6-digit OTP" maxlength="6" required />
            <button type="submit">Verify</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
