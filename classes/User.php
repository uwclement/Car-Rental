<?php
/**
 * User Class for user authentication and profile management
 */
class User {
    private $db;
    private $id;
    private $name;
    private $email;
    private $role;
    
    /**
     * Constructor
     * @param Database $database
     */
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Authenticate user login
     * @param string $email
     * @param string $password
     * @return bool
     */
    public function login($email, $password) {
        $email = $this->db->escapeString($email);
        
        $sql = "SELECT * FROM users WHERE email = '{$email}'";
        $result = $this->db->query($sql);
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                $this->id = $user['id'];
                $this->name = $user['name'];
                $this->email = $user['email'];
                $this->role = $user['role'];
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Register new user
     * @param array $userData
     * @return int|bool
     */
    public function register($userData) {
        $name = $this->db->escapeString($userData['name']);
        $email = $this->db->escapeString($userData['email']);
        $password = password_hash($userData['password'], PASSWORD_DEFAULT);
        $phone = isset($userData['phone']) ? $this->db->escapeString($userData['phone']) : '';
        $address = isset($userData['address']) ? $this->db->escapeString($userData['address']) : '';
        $drivingLicense = isset($userData['driving_license']) ? $this->db->escapeString($userData['driving_license']) : '';
        
        // Check if email already exists
        $checkSql = "SELECT * FROM users WHERE email = '{$email}'";
        $checkResult = $this->db->query($checkSql);
        
        if ($checkResult->num_rows > 0) {
            return false;
        }
        
        $sql = "INSERT INTO users (name, email, password, phone, address, driving_license) 
                VALUES ('{$name}', '{$email}', '{$password}', '{$phone}', '{$address}', '{$drivingLicense}')";
        
        if ($this->db->query($sql)) {
            return $this->db->getLastId();
        }
        
        return false;
    }
    
    /**
     * Get user by ID
     * @param int $id
     * @return array|null
     */
    public function getUserById($id) {
        $id = (int) $id;
        
        $sql = "SELECT * FROM users WHERE id = {$id}";
        $result = $this->db->query($sql);
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Get user by email
     * @param string $email
     * @return array|null
     */
    public function getUserByEmail($email) {
        $email = $this->db->escapeString($email);
        
        $sql = "SELECT * FROM users WHERE email = '{$email}'";
        $result = $this->db->query($sql);
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Get all users
     * @return array
     */
    public function getAllUsers() {
        $sql = "SELECT * FROM users ORDER BY created_at DESC";
        $result = $this->db->query($sql);
        $users = [];
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }
        
        return $users;
    }
    
    /**
     * Update user profile
     * @param int $userId
     * @param array $userData
     * @return bool
     */
    public function updateProfile($userId, $userData) {
        $userId = (int) $userId;
        $name = $this->db->escapeString($userData['name']);
        $phone = $this->db->escapeString($userData['phone']);
        $address = $this->db->escapeString($userData['address']);
        $drivingLicense = $this->db->escapeString($userData['driving_license']);
        
        $sql = "UPDATE users SET name = '{$name}', phone = '{$phone}', 
                address = '{$address}', driving_license = '{$drivingLicense}' 
                WHERE id = {$userId}";
        
        return $this->db->query($sql);
    }
    
    /**
     * Delete user
     * @param int $userId
     * @return bool
     */
    public function deleteUser($userId) {
        $userId = (int) $userId;
        
        $sql = "DELETE FROM users WHERE id = {$userId}";
        
        return $this->db->query($sql);
    }
    
    /**
     * Check if user is admin
     * @param int $userId
     * @return bool
     */
    public function isAdmin($userId) {
        $userId = (int) $userId;
        
        $sql = "SELECT role FROM users WHERE id = {$userId}";
        $result = $this->db->query($sql);
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            return $user['role'] === 'admin';
        }
        
        return false;
    }
}