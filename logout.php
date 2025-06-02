<?php
session_start();

// Import config array
$config = require_once 'config/settings-configuration.php';

// Include Database class
require_once 'database/dbconnection.php';

try {
    // Instantiate Database with config
    $db = new Database($config);
    $conn = $db->dbConnection();

    // Set PDO error mode to exception for better error handling
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $user_id = $_SESSION['user_id'] ?? null;

    if ($user_id) {
        $logout_time = date('Y-m-d H:i:s');

        $sql = "INSERT INTO logs (user_id, activity, created_at) VALUES (:user_id, 'logout', :created_at)";
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':created_at', $logout_time, PDO::PARAM_STR);

        $stmt->execute();
    }
} catch (PDOException $e) {
    // Log error to server log but don't expose to user
    error_log("Logout log error: " . $e->getMessage());
}

// Destroy session regardless of logging success
session_unset();
session_destroy();

header("Location: login.php");
exit;
