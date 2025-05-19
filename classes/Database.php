<?php
/**
 * Database Class for handling database connection and operations
 */
class Database {
    private $host = 'localhost';
    private $username = 'root';
    private $password = '';
    private $database = 'car_rental';
    private $conn;
    
    /**
     * Constructor - Establish database connection
     */
    public function __construct() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
        
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }
    
    /**
     * Get database connection
     * @return mysqli
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Execute query
     * @param string $sql
     * @return mysqli_result|bool
     */
    public function query($sql) {
        return $this->conn->query($sql);
    }
    
    /**
     * Get last inserted ID
     * @return int
     */
    public function getLastId() {
        return $this->conn->insert_id;
    }
    
    /**
     * Escape string for SQL injection prevention
     * @param string $string
     * @return string
     */
    public function escapeString($string) {
        return $this->conn->real_escape_string($string);
    }
    
    /**
     * Close database connection
     */
    public function __destruct() {
        $this->conn->close();
    }
}