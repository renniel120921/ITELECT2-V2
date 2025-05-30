<?php
session_start();
require_once 'vendor/autoload.php';
require_once 'database/dbconnection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $stmt = $db->prepare("SELECT id FROM user WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $otp = rand(100000, 999999);
        $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        $stmt = $db->prepare("UPDATE user SET otp = :otp, otp_expiry = :expiry WHERE id = :id");
        $stmt->execute([':otp' => $otp, ':expiry' => $expiry, ':id' => $user['id']]);

        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->Host = 'smtp.yourhost.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your_email@example.com';
        $mail->Password = 'your_email_password';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('no-reply@yourdomain.com', 'Your Website');
        $mail->addAddress($email);
        $mail->Subject = 'Your Password Reset OTP';
        $mail->Body = "Your OTP for password reset is: $otp. It expires in 15 minutes.";

        if ($mail->send()) {
            $_SESSION['reset_email'] = $email;
            $_SESSION['success_message'] = 'OTP sent to your email. Check your inbox.';
            header("Location: verify-otp.php");
            exit;
        } else {
            $error_message = 'Failed to send OTP email. Try again later.';
        }
    } else {
        $error_message = 'Email not found!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Forgot Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center min-vh-100">
  <div class="card shadow-sm p-4" style="max-width: 400px; width: 100%;">
    <h3 class="mb-4 text-center">Forgot Password</h3>

    <?php if (!empty($error_message)) : ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="mb-3">
        <label for="email" class="form-label">Registered Email</label>
        <input
          type="email"
          class="form-control"
          id="email"
          name="email"
          placeholder="Enter your registered email"
          required
        />
      </div>
      <button type="submit" class="btn btn-primary w-100">Send OTP</button>
    </form>
  </div>
</div>

</body>
</html>
