<?php
session_start();
require_once 'admin-class.php';

$admin = new ADMIN();

if (isset($_POST['btn-signup'])) {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Instead of sendOtp directly, your workflow might differ
    // But basically call the signup logic here
    $admin->sendOtp(rand(100000, 999999), $email);
    // or if you want to add admin directly, call $admin->addAdmin(...)
}

if (isset($_POST['btn-signin'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    $admin->adminSignin($email, $password, $csrf_token);
}
?>
