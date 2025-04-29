<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__.'/../database/dbconnection.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if (empty($_SESSION['csrf_token'])) {
    $csrf_token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $csrf_token;
}else{
    $csrf_token = $_SESSION['csrf_token'];
}

class SystemConfig {

    private $conn;
    private $smtp_email;
    private $smtp_password;


    public function __construct()
    {
        $database = new Database();
        $db = $database->dbconnection();
        $this->conn = $db;

        //get email config
        $stmt = $this->runQuery("SELECT * FROM email_config");
        $stmt->execute();
        $email_config = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->smtp_email = $email_config['email'];
        $this->smtp_password = $email_config['password'];
    }

    public function getSmtpEmail(){
        return $this->smtp_email;
    }

    public function getSmtpPassword(){
        return $this->smtp_Pasword;
    }

    public function runQuery($sql){
        $stmt = $this->conn->prepare($sql);
        return $stmt;
    }
}
?>
