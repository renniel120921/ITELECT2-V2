<?php
require_once 'config/settings-configuration.php'; // Include config

class Database
{
    private $host;
    private $port;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct(array $config = [])
    {
        // Use provided config or set default empty values
        $this->host     = $config['host'] ?? 'localhost';
        $this->port     = $config['port'] ?? '3306';
        $this->db_name  = $config['dbname'] ?? '';
        $this->username = $config['username'] ?? '';
        $this->password = $config['password'] ?? '';
    }

    public function dbConnection()
    {
        $this->conn = null;

        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name}";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            die("Connection error: " . $exception->getMessage());
        }

        return $this->conn;
    }
}
?>
