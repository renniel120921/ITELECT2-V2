<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['email_for_otp'])) {
    header("Location: signup.php");
    exit;
}

$email = $_SESSION['email_for_otp'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_otp = trim($_POST['otp']);

    $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ? AND otp = ? AND otp_expiry > NOW() AND status = 0");
    $stmt->execute([$email, $entered_otp]);
    $user = $stmt->fetch();

    if ($user) {
        // OTP correct & valid
        $stmt_update = $pdo->prepare("UPDATE user SET status = 1, otp = NULL, otp_expiry = NULL WHERE email = ?");
        $stmt_update->execute([$email]);
        unset($_SESSION['email_for_otp']);
        header("Location: login.php?verified=1");
        exit;
    } else {
        $error = "Invalid or expired OTP.";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Verify OTP</title></head>
<body>
<h2>Enter OTP sent to <?= htmlspecialchars($email) ?></h2>
<?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
<form method="post" action="">
    OTP: <input type="text" name="otp" maxlength="6" required><br><br>
    <button type="submit">Verify</button>
</form>
</body>
</html>
