<?php
session_start();
require_once 'database/dbconnection.php';

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot-password.php");
    exit;
}

$email = $_SESSION['reset_email'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input_otp = trim($_POST['otp']);

    $stmt = $db->prepare("SELECT otp, otp_expiry FROM user WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $current_time = date('Y-m-d H:i:s');
        if ($input_otp === $user['otp'] && $current_time <= $user['otp_expiry']) {
            $_SESSION['otp_verified'] = true;
            header("Location: reset-password.php");
            exit;
        } else {
            $error_message = 'Invalid or expired OTP.';
        }
    } else {
        $error_message = 'No OTP record found.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Verify OTP</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center min-vh-100">
  <div class="card shadow-sm p-4" style="max-width: 400px; width: 100%;">
    <h3 class="mb-4 text-center">Verify OTP</h3>

    <?php if (!empty($error_message)) : ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php elseif (!empty($_SESSION['success_message'])): ?>
      <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success_message']) ?></div>
      <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="mb-3">
        <label for="otp" class="form-label">Enter OTP</label>
        <input
          type="text"
          class="form-control"
          id="otp"
          name="otp"
          placeholder="6-digit OTP"
          required
          maxlength="6"
          pattern="\d{6}"
        />
      </div>
      <button type="submit" class="btn btn-primary w-100">Verify OTP</button>
    </form>
  </div>
</div>

</body>
</html>
