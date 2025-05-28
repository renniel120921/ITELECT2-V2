<?php
session_start();
include_once 'config/settings-configuration.php';

if (isset($_POST['btn-login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM user WHERE email = :email");
    $stmt->execute([':email' => $email]);

    if ($stmt->rowCount() === 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: dashboard.php");
            exit;
        } else {
            $_SESSION['error'] = "Incorrect password.";
            header("Location: login.php");
            exit;
        }
    } else {
        $_SESSION['error'] = "User not found.";
        header("Location: login.php");
        exit;
    }
}
?>
