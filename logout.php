<?php
session_start();

// Import config array
$config = require_once 'config/settings-configuration.php';

// Include Database class
require_once 'database/dbconnection.php';

// Instantiate Database with config
$db = new Database($config);
$conn = $db->dbConnection();

$user_id = $_SESSION['user_id'] ?? null;

if ($user_id) {
    $logout_time = date('Y-m-d H:i:s');

    $sql = "INSERT INTO logs (user_id, activity, created_at) VALUES (:user_id, 'logout', :created_at)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Prepare failed");
    } else {
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':created_at', $logout_time, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            error_log("Execute failed: " . implode(", ", $stmt->errorInfo()));
        }
    }
}

session_unset();
session_destroy();

header("Location: login.php");
exit;
