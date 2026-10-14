<?php
class Database {
    private $host = 'localhost';
    private $port = '5432';
    private $db_name = 'aduin_db'; // Sesuaikan nama database PostgreSQL kamu
    private $username = 'postgres'; // Sesuaikan username database kamu
    private $password = 'y4sUd4hl4h';         // Sesuaikan password database kamu
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $dsn = "pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name;
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch(PDOException $exception) {
            echo json_encode([
                "success" => false, 
                "message" => "Koneksi database error: " . $exception->getMessage()
            ]);
            exit();
        }
        return $this->conn;
    }
}