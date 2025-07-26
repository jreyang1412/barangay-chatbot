<?php
// config/database.php

require_once __DIR__ . '/../vendor/autoload.php'; // Load Composer autoload

use Dotenv\Dotenv;

// Load .env variables (if running locally)
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    public $conn;

    public function __construct() {
        // Get Supabase credentials from environment variables
        $this->host     = $_ENV['db_host']     ?? 'db.kvqbfffydynntfxqkbxn.supabase.co';
        $this->db_name  = $_ENV['db_name']     ?? 'postgres';
        $this->username = $_ENV['db_user']     ?? 'postgres';
        $this->password = $_ENV['db_pass']     ?? 'ET3h9k4AabU219lG';
        $this->port     = $_ENV['db_port']     ?? '6543'; // Supabase pooler port
    }

    public function getConnection() {
        $this->conn = null;
        
        try {
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db_name};sslmode=require";
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
