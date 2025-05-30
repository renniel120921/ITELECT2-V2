<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head><title>Verify OTP</title></head>
<body>

<h2>Enter OTP sent to your email</h2>

<?php
if (isset($_SESSION['error'])) {
    echo '<p style="color:red;">'.htmlspecialchars($_SESSION['error']).'</p>';
    unset($_SESSION['error']);
}
if (isset($_SESSION['message'])) {
    echo '<p style="color:green;">'.htmlspecialchars($_SESSION['message']).'</p>';
    unset($_SESSION['message']);
}
?>

<form method="POST" action="verify_otp.php">
    <label for="otp">OTP Code:</label>
    <input type="text" id="otp" name="otp" maxlength="6" required autofocus>
    <button type="submit">Verify</button>
</form>

</body>
</html>
