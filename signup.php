<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'itelect2');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email exists
    $check = $conn->query("SELECT * FROM user WHERE email='$email'");
    if ($check->num_rows > 0) {
        echo "Email already registered.";
        exit;
    }

    // Generate OTP (6 digits)
    $otp = rand(100000, 999999);

    // Insert user with OTP and otp_verified = false
    $sql = "INSERT INTO user (username, email, password, otp, otp_verified) VALUES ('$username', '$email', '$password', '$otp', 0)";
    if ($conn->query($sql)) {
        // Send OTP email
        $subject = "Your OTP Code";
        $message = "Your OTP code is: $otp";
        $headers = "From: noreply@example.com\r\n";

        if (mail($email, $subject, $message, $headers)) {
            $_SESSION['email'] = $email;  // Save email in session to verify OTP
            header("Location: verify_otp.php");
            exit;
        } else {
            echo "Failed to send OTP email.";
        }
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<form method="POST">
    Username: <input type="text" name="username" required><br>
    Email: <input type="email" name="email" required><br>
    Password: <input type="password" name="password" required><br>
    <button type="submit">Sign Up</button><?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'itelect2');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $conn->query("SELECT * FROM user WHERE email='$email'");
    if ($check->num_rows > 0) {
        echo "<p class='error'>Email already registered.</p>";
    } else {
        $otp = rand(100000, 999999);
        $sql = "INSERT INTO user (username, email, password, otp, otp_verified) VALUES ('$username', '$email', '$password', '$otp', 0)";
        if ($conn->query($sql)) {
            $subject = "Your OTP Code";
            $message = "Your OTP code is: $otp";
            $headers = "From: noreply@example.com\r\n";

            if (mail($email, $subject, $message, $headers)) {
                $_SESSION['email'] = $email;
                header("Location: verify-otp.php");
                exit;
            } else {
                echo "<p class='error'>Failed to send OTP email.</p>";
            }
        } else {
            echo "<p class='error'>Error: " . $conn->error . "</p>";
        }
    }
}
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
    <form method="POST" autocomplete="off">
        <input type="text" name="username" placeholder="Username" required />
        <input type="email" name="email" placeholder="Email" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit">Sign Up</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
</div>
</body>
</html>

</form>
<p>Already have an account? <a href="login.php">Login here</a></p>

