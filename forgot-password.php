<?php
require_once 'database/dbconnection.php';

$db = new Database();
$conn = $db->dbConnection(); // ← this is the missing part!

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT * FROM user WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Add reset_token_expiration column if not present in your table
        $stmt = $conn->prepare("UPDATE user SET token_code = :token, created_at = :expires WHERE email = :email");
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expires', $expires);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $resetLink = "http://localhost/ITELECT2-V2/reset-password.php?token=" . $token;
        $msg = "Reset link sent! (For demo: <a href='$resetLink' target='_blank'>$resetLink</a>)";
    } else {
        $msg = "<span style='color:red;'>No account found with that email.</span>";
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
            background: linear-gradient(145deg, #f0f0f0, #e0e0e0);
            display: flex;
            height: 100vh;
            justify-content: center;
            align-items: center;
        }
        .container {
            background: white;
            padding: 2rem 3rem;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            width: 400px;
        }
        h2 {
            margin-bottom: 20px;
            color: #1e90ff;
            text-align: center;
        }
        label {
            font-weight: bold;
            margin-bottom: 6px;
            display: block;
        }
        input[type="email"] {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-bottom: 20px;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #1e90ff;
            color: #fff;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background: #0d74d1;
        }
        .message {
            margin-top: 15px;
            font-size: 0.95rem;
            color: #333;
            text-align: center;
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
