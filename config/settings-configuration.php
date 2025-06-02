<?php
declare(strict_types=1);

// Enable error reporting during development
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Localhost Configuration
$local_config = [
    'host'     => 'localhost',
    'port'     => '3306',
    'dbname'   => 'itelect2',
    'username' => 'root',
    'password' => ''
];

// Production Configuration (update with actual prod credentials if needed)
$production_config = [
    'host'     => 'localhost', // Change this if using cloud/remote DB
    'port'     => '3306',
    'dbname'   => 'itelect2',
    'username' => 'root',
    'password' => ''
];

// Detect environment
$server_name = $_SERVER['SERVER_NAME'] ?? '';
$server_addr = $_SERVER['SERVER_ADDR'] ?? '';

$is_local = in_array($server_name, ['localhost', '127.0.0.1', '192.168.1.72'], true) ||
            in_array($server_addr, ['127.0.0.1', '192.168.1.72'], true);

// Use local or production config
$config = $is_local ? $local_config : $production_config;

// Fallback check (prevent empty config from crashing app)
if (empty($config['host']) || empty($config['dbname']) || empty($config['username'])) {
    die('Database configuration is invalid.');
}

return $config;
