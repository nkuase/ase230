<?php

class SessionAuth {
    
    public function __construct() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    // Log in a user (create session)
    public function login_user($user) {
        // Regenerate session ID for security (prevents session fixation)
        session_regenerate_id(true);
        
        // Store user information in session
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['login_time'] = time();
        
        return true;
    }
    
    // Check if user is logged in
    public function is_logged_in() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    // Get current logged-in user info
    public function get_current_user() {
        if (!$this->is_logged_in()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'login_time' => $_SESSION['login_time']
        ];
    }
    
    // Logout user (destroy session)
    public function logout() {
        // Clear session data
        $_SESSION = [];
        
        // Destroy session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destroy session
        session_destroy();
        
        return true;
    }
    
    // Require authentication (redirect to login if not logged in)
    public function require_auth($redirect_url = 'login.php') {
        if (!$this->is_logged_in()) {
            header("Location: $redirect_url");
            exit;
        }
        
        // Check for session timeout (30 minutes = 1800 seconds)
        if (time() - $_SESSION['login_time'] > 1800) {
            $this->logout();
            header("Location: $redirect_url?timeout=1");
            exit;
        }
        
        // Update last activity time
        $_SESSION['login_time'] = time();
    }
    
    // Check if user is guest (not logged in)
    public function is_guest() {
        return !$this->is_logged_in();
    }
    
    // Get session info (for debugging)
    public function get_session_info() {
        return [
            'session_id' => session_id(),
            'logged_in' => $this->is_logged_in(),
            'user_id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['username'] ?? null,
            'login_time' => $_SESSION['login_time'] ?? null,
            'session_age' => isset($_SESSION['login_time']) ? time() - $_SESSION['login_time'] : null
        ];
    }
    
    // Require guest (redirect if logged in)
    public function require_guest($redirect_url = 'dashboard.php') {
        if ($this->is_logged_in()) {
            header("Location: $redirect_url");
            exit;
        }
    }
}
