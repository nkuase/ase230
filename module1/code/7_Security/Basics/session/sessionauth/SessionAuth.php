<?php
/**
 * SessionAuth Class - Secure Session Management
 */
class SessionAuth {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function login_user($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['logged_in'] = true;
        
        // Security: Prevent session fixation
        session_regenerate_id(true);
    }
    
    public function logout_user() {
        session_unset();   // Clear data
        session_destroy(); // Destroy session
    }
    
    public function is_logged_in() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public function get_user() {
        if ($this->is_logged_in()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username']
            ];
        }
        return null;
    }
    
    public function require_auth($login_url = 'login.php') {
        if (!$this->is_logged_in()) {
            header("Location: $login_url");
            exit;
        }
    }
}
