<?php
session_start();

$conn = new mysqli('localhost', 'root', '', 'itelect2');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $error = "Invalid email format.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, email, password FROM user WHERE email = ? AND otp_verified = 1 LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();

                if (password_verify($password, $user['password'])) {
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['logged_in'] = true;

                    $stmt->close();
                    $conn->close();

                    header("Location: dashboard/admin/dashboard.php");
                    exit;
                } else {
                    $error = "Incorrect password.";
                }
            } else {
                $error = "User not found or email not verified.";
            }
            $stmt->close();
        } else {
            $error = "Database error: " . $conn->error;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
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

    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php elseif ($success): ?>
        <p class="success"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <form method="POST" autocomplete="off" novalidate>
        <input type="email" name="email" placeholder="Email" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
    <p><a href="forgot-password.php">Forgot Password?</a></p>
</div>
</body>
</html>
