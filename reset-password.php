<?php
session_start();
require_once 'database/dbconnection.php';

if (!isset($_SESSION['reset_email']) || !isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
    header("Location: forgot-password.php");
    exit;
}

$email = $_SESSION['reset_email'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error_message = 'Passwords do not match!';
    } elseif (strlen($password) < 6) {
        $error_message = 'Password must be at least 6 characters.';
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE user SET password = :password, otp = NULL, otp_expiry = NULL WHERE email = :email");
        $stmt->execute([':password' => $password_hash, ':email' => $email]);

        unset($_SESSION['reset_email'], $_SESSION['otp_verified']);

        $_SESSION['success_message'] = 'Password reset successful! You can now login.';
        header("Location: signin.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Reset Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center min-vh-100">
  <div class="card shadow-sm p-4" style="max-width: 400px; width: 100%;">
    <h3 class="mb-4 text-center">Reset Password</h3>

    <?php if (!empty($error_message)) : ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php elseif (!empty($_SESSION['success_message'])): ?>
      <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success_message']) ?></div>
      <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="mb-3">
        <label for="password" class="form-label">New Password</label>
        <input
          type="password"
          class="form-control"
          id="password"
          name="password"
          placeholder="Enter new password"
          required
          minlength="6"
        />
      </div>
      <div class="mb-3">
        <label for="confirm_password" class="form-label">Confirm New Password</label>
        <input
          type="password"
          class="form-control"
          id="confirm_password"
          name="confirm_password"
          placeholder="Confirm new password"
          required
          minlength="6"
        />
      </div>
      <button type="submit" class="btn btn-primary w-100">Reset Password</button>
    </form>
  </div>
</div>

</body>
</html>
