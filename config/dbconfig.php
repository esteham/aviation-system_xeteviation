<?php
/**
 * Database Configuration Class
 * Handles database connection and provides PDO instance
 */
class DBConfig {
    private static $instance = null;
    private $pdo;
    
    // Database configuration - should be in environment variables in production
    private $db_host = 'localhost';
    private $db_name = 'u975049586_aviation';
    private $db_user = 'u975049586_aviation';
    private $db_pass = 'Dipannita.6jet';
    private $db_charset = 'utf8mb4';
    
    private function __construct() {
        try {
            $dsn = "mysql:host={$this->db_host};dbname={$this->db_name};charset={$this->db_charset}";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT        => true
            ];
            
            $this->pdo = new PDO($dsn, $this->db_user, $this->db_pass, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection error. Please try again later.");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    // Prevent cloning and serialization
    private function __clone() {}
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Create global database connection
try {
    $db = DBConfig::getInstance()->getConnection();
} catch (Exception $e) {
    // Handle error (could redirect to maintenance page)
    die("System maintenance in progress. Please try again later.");
}
?>