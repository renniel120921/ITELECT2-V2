<?php
require_once 'config/settings-configuration.php';
require_once 'database/dbconnection.php';

class Admin
{
    private PDO $conn;

    public function __construct(PDO $pdo)
    {
        $this->conn = $pdo;
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /**
     * Generate CSRF token and store in session
     */
    public function generateCSRFToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    /**
     * Validate CSRF token
     */
    public function validateCSRFToken(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Login as admin
     */
    public function loginAdmin(string $username, string $password, string $csrfToken): bool
    {
        if (!$this->validateCSRFToken($csrfToken)) {
            return false;
        }

        $sql = "SELECT * FROM admin WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['username' => $username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            return true;
        }

        return false;
    }

    /**
     * Register a new user and send OTP
     */
    public function createUser(string $username, string $email, string $password, string $csrfToken): string|bool
    {
        if (!$this->validateCSRFToken($csrfToken)) {
            return "Invalid CSRF token.";
        }

        $checkSql = "SELECT id FROM user WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($checkSql);
        $stmt->execute(['email' => $email]);

        if ($stmt->fetch()) {
            return "Email is already registered.";
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $otp = random_int(100000, 999999);

        $insertSql = "
            INSERT INTO user (username, email, password, otp, otp_verified)
            VALUES (:username, :email, :password, :otp, 0)";
        $stmt = $this->conn->prepare($insertSql);
        $success = $stmt->execute([
            'username' => $username,
            'email'    => $email,
            'password' => $hashedPassword,
            'otp'      => $otp
        ]);

        if ($success) {
            if ($this->sendOTP($email, $otp)) {
                return true;
            }
            return "User created but failed to send OTP.";
        }

        return "Failed to create user.";
    }

    /**
     * Send OTP via email
     */
    public function sendOTP(string $email, int $otp): bool
    {
        $subject = "Your OTP Code";
        $message = "Hello,\n\nYour OTP code is: $otp\n\nPlease enter this to verify your email.";
        $headers = "From: noreply@example.com\r\n" .
                   "Reply-To: noreply@example.com\r\n" .
                   "X-Mailer: PHP/" . phpversion();

        return mail($email, $subject, $message, $headers);
    }

    /**
     * Verify OTP
     */
    public function verifyOTP(string $email, int $otp): bool
    {
        $sql = "SELECT id FROM user WHERE email = :email AND otp = :otp AND otp_verified = 0 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'email' => $email,
            'otp'   => $otp
        ]);

        if ($stmt->fetch()) {
            $updateSql = "UPDATE user SET otp_verified = 1 WHERE email = :email";
            $updateStmt = $this->conn->prepare($updateSql);
            return $updateStmt->execute(['email' => $email]);
        }

        return false;
    }

    /**
     * Get list of users
     */
    public function getUsers(): array
    {
        $sql = "SELECT id, username, email, otp_verified FROM user";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Delete a user by ID
     */
    public function deleteUser(int $userId, string $csrfToken): bool
    {
        if (!$this->validateCSRFToken($csrfToken)) {
            return false;
        }

        $sql = "DELETE FROM user WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute(['id' => $userId]);
    }

    /**
     * Admin logout
     */
    public function logout(): void
    {
        session_unset();
        session_destroy();
    }
}
