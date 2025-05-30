<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'itelect2');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['email'])) {
    header("Location: signup.php");
    exit;
}

$email = $_SESSION['email'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_otp = $_POST['otp'];

    $result = $conn->query("SELECT otp FROM users WHERE email='$email' AND otp_verified=0");
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if ($row['otp'] === $user_otp) {
            // Update otp_verified
            $conn->query("UPDATE users SET otp_verified=1, otp=NULL WHERE email='$email'");
            unset($_SESSION['email']);
            echo "OTP verified successfully! Redirecting to login...";
            header("refresh:3;url=login.php");
            exit;
        } else {
            echo "Invalid OTP.";
        }
    } else {
        echo "No OTP pending verification or already verified.";
    }
}
?>

<form method="POST">
    Enter OTP: <input type="text" name="otp" required><br>
    <button type="submit">Verify OTP</button>
</form>
