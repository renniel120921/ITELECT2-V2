<?php
require_once 'config/settings-configuration.php'; // Make sure this file returns config as an array

class Database
{
    private string $host;
    private string $port;
    private string $db_name;
    private string $username;
    private string $password;
    private ?PDO $conn = null;

    public function __construct(array $config = [])
    {
        // Use provided config or fallback to settings-configuration.php
        $this->host     = $config['host'] ?? DB_HOST ?? 'localhost';
        $this->port     = $config['port'] ?? DB_PORT ?? '3306';
        $this->db_name  = $config['dbname'] ?? DB_NAME ?? 'itelect2';
        $this->username = $config['username'] ?? DB_USER ?? 'root';
        $this->password = $config['password'] ?? DB_PASS ?? '';
    }

    public function dbConnection(): ?PDO
    {
        if ($this->conn !== null) {
            // Return existing connection if already connected
            return $this->conn;
        }

        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            // Don't expose raw error messages in production, but here for dev:
            die("Database connection failed: " . $e->getMessage());
        }

        return $this->conn;
    }
}
