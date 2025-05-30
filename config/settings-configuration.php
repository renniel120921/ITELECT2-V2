<?php
// settings-configuration.php

// LOCALHOST SETTINGS
$config = [
    'host'     => 'localhost',
    'port'     => '3307',
    'dbname'   => 'itelec2',
    'username' => 'root',
    'password' => ''
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
