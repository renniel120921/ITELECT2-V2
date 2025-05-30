<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'itelect2');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM user WHERE email='$email' AND otp_verified=1");
    if ($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['logged_in'] = true;

            // Redirect to dashboard
            header("Location: dashboard/admin/dashboard.php");
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "User not found or email not verified.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Login</title>
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
    .success {
        background-color: #00cc6a;
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 15px;
        font-weight: 600;
    }
</style>
</head>
<body>
<div class="container">
    <h2>Login</h2>

    <?php if($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php elseif($success): ?>
        <p class="success"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
        <input type="email" name="email" placeholder="Email" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
    <p><a href="forgot-password.php">Forgot Password?</a></p>
</div>
</body>
</html>
