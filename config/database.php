<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'itelect2';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function dbConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8",
                $this->username,
                $this->password
            );

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
            exit;
        }

        return $this->conn;
    }
}
