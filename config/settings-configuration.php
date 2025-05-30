<?php
// Simulan ang session kung wala pa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ipakita ang lahat ng errors (pang-debug lang ito, wag gamitin sa production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Gumawa ng CSRF token kung wala pa
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
} else {
    $csrf_token = $_SESSION['csrf_token'];
}

// Class para sa system configuration
class SystemConfig
{
    private $conn;             // Database connection
    private $smtp_email;       // Email address para sa SMTP
    private $smtp_password;    // Password ng email

    // Constructor - kusang tinatawag kapag ginawa ang object
    public function __construct()
    {
        // Kumonekta sa database gamit ang Database class
        $database = new Database();
        $this->conn = $database->dbConnection();

        // Kunin ang email settings mula sa database
        $stmt = $this->runQuery("SELECT * FROM email_config");
        $stmt->execute();
        $email_config = $stmt->fetch(PDO::FETCH_ASSOC);

        // I-save ang email at password mula sa database
        $this->smtp_email = $email_config['email'];
        $this->smtp_password = $email_config['password'];
    }

    // Getter function para kunin ang SMTP email
    public function getSmtpEmail()
    {
        return $this->smtp_email;
    }

    // Getter function para kunin ang SMTP password
    public function getSmtpPassword()
    {
        return $this->smtp_password;
    }

    // Function para maghanda ng SQL query
    public function runQuery($sql)
    {
        return $this->conn->prepare($sql);
    }
}
