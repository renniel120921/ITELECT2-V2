<?php
// settings-configuration.php

// LOCALHOST SETTINGS
$local_config = [
    'host'     => 'localhost',
    'port'     => '3306',      // Make sure this is the port your MySQL runs on
    'dbname'   => 'itelect2',
    'username' => 'root',
    'password' => ''
];

// PRODUCTION SETTINGS - update with your real production creds
$production_config = [
    'host'     => 'host',
    'port'     => '3306',
    'dbname'   => 'itelect2',
    'username' => 'root',
    'password' => ''
];

// Detect environment
$server_name = $_SERVER['SERVER_NAME'] ?? '';
$server_addr = $_SERVER['SERVER_ADDR'] ?? '';

if ($server_name === 'localhost' || $server_addr === '127.0.0.1' || $server_name === '192.168.1.72') {
    $config = $local_config;
} else {
    $config = $production_config;
}
