<?php
// Authentication Class
require_once __DIR__ . '/Database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function login($username, $password) {
        // Sanitize username
        $username = trim($username);
        
        // Get user from database
        $user = $this->db->selectOne(
            "SELECT * FROM users WHERE username = :username AND status = 'active'",
            ['username' => $username]
        );
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Update last login
            $this->db->update(
                'users',
                ['last_login' => date('Y-m-d H:i:s')],
                'id = :id',
                ['id' => $user['id']]
            );
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['department'] = $user['department'];
            $_SESSION['logged_in'] = true;
            
            // Log activity
            $this->logActivity($user['id'], 'login', 'User logged in');
            
            return true;
        }
        
        // Log failed attempt
        error_log("Failed login attempt for username: $username");
        return false;
    }
    
    public function logout() {
        if ($this->isLoggedIn()) {
            $this->logActivity($_SESSION['user_id'], 'logout', 'User logged out');
        }
        
        // Clear session
        $_SESSION = [];
        session_destroy();
        
        return true;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }
    
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return $this->db->selectOne(
            "SELECT * FROM users WHERE id = :id",
            ['id' => $_SESSION['user_id']]
        );
    }
    
    public function hasRole($roles) {
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        return $this->isLoggedIn() && in_array($_SESSION['role'], $roles);
    }
    
    public function requireRole($roles) {
        if (!$this->hasRole($roles)) {
            setFlashMessage('error', 'You do not have permission to access this page.');
            redirect('dashboard.php');
        }
    }
    
    private function logActivity($userId, $action, $description = null) {
        $this->db->insert('activity_logs', [
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
    }
    
    public function changePassword($userId, $currentPassword, $newPassword) {
        $user = $this->db->selectOne(
            "SELECT password_hash FROM users WHERE id = :id",
            ['id' => $userId]
        );
        
        if ($user && password_verify($currentPassword, $user['password_hash'])) {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $this->db->update(
                'users',
                ['password_hash' => $newHash],
                'id = :id',
                ['id' => $userId]
            );
            return true;
        }
        
        return false;
    }
}
?>