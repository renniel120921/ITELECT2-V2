<?php

class Database
{
    private $host;
    private $port = "3306";
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct()
    {
        // Localhost config (development)
        if (in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1', '192.168.1.72'])) {
            $this->host = "localhost";
            $this->db_name = "itelect2";
            $this->username = "root";
            $this->password = "";
        } else {
            // Pang-production (palitan kapag idedeploy mo na)
            $this->host = "localhost";
            $this->db_name = "";
            $this->username = "";
            $this->password = "";
        }
    }

    // Function para kumonekta sa database
    public function dbConnection()
    {
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};port={$this->port};dbname={$this->db_name}",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }

        return $this->conn;
    }
}
?>
