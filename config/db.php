<?php
// config/db.php

$host = 'localhost';
$db   = 'itelect2';  // Your DB name
$user = 'root';       // Your DB user
$pass = '';           // Your DB password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}
