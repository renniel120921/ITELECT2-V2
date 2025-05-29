<?php
require_once'database/dbcon.php';

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT * FROM user WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $conn->prepare("UPDATE user SET token_code = :token, reset_token_expiration = :expires WHERE email = :email");
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expires', $expires);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $resetLink = "http://yourdomain.com/reset-password.php?token=" . $token;
        $msg = "Reset link sent! (For demo: <a href='$resetLink'>$resetLink</a>)";
    } else {
        $msg = "No account found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f2f2f2;
            display: flex;
            height: 100vh;
            justify-content: center;
            align-items: center;
        }
        .container {
            background: white;
            padding: 2rem 3rem;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 400px;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        input[type="email"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        button {
            margin-top: 20px;
            background: #1e90ff;
            color: white;
            border: none;
            padding: 10px 15px;
            width: 100%;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        .message {
            margin-top: 15px;
            color: #555;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Forgot Password</h2>
    <form method="POST">
        <label for="email">Enter your email:</label>
        <input type="email" name="email" required>
        <button type="submit">Send Reset Link</button>
    </form>
    <div class="message"><?= $msg ?></div>
</div>
</body>
</html>
