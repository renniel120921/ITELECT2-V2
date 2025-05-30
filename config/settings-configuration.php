<?php
// settings-configuration.php

// LOCALHOST SETTINGS
$local_config = [
    'host'     => 'localhost',
    'port'     => '3307',
    'dbname'   => 'itelec2',
    'username' => 'root',
    'password' => ''
];

// PRODUCTION SETTINGS
$production_config = [
    'host'     => 'localhost',             // Change as needed
    'port'     => '3306',
    'dbname'   => 'itelect2',    // Replace with actual name
    'username' => 'root',         // Replace with actual username
    'password' => ''          // Replace with actual password
];

// ENVIRONMENT DETECTION
$server_name = $_SERVER['SERVER_NAME'] ?? '';
$server_addr = $_SERVER['SERVER_ADDR'] ?? '';

if ($server_name === 'localhost' || $server_addr === '127.0.0.1' || $server_name === '192.168.1.72') {
    $config = $local_config;
} else {
    $config = $production_config;
}
?>
