<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    public $conn;

    public function __construct() {
        // Get environment variables with proper Supabase host format
        $this->host = 'db.jwwfjzccalvcswgdhrtr.supabase.co'; // Hardcoded for now
        $this->db_name = $_ENV['DB_NAME'] ?? 'postgres';
        $this->username = $_ENV['DB_USER'] ?? 'postgres';
        $this->password = $_ENV['DB_PASS'] ?? 'wY/?zx_8w3MfU-t';
        $this->port = $_ENV['DB_PORT'] ?? '5432';
    }

    public function getConnection() {
        $this->conn = null;
        
        try {
            // For Supabase PostgreSQL connections, include sslmode=require
            $dsn = "pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";sslmode=require";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            throw new Exception("Database connection failed");
        }
        
        return $this->conn;
    }
}
?>