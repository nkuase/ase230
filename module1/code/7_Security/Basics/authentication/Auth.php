<?php

require_once __DIR__ . '/JsonDatabase.php';

class Auth {
    private $users_db;
    
    public function __construct($users_file = 'data/users.json') {
        $this->users_db = new JsonDatabase($users_file);
    }
    
    // Register a new user
    public function register($username, $password, $email = null) {
        // Check if username already exists
        if ($this->find_user_by_username($username)) {
            throw new Exception('Username already exists');
        }
        
        // Check if email already exists (if provided)
        if ($email && $this->find_user_by_email($email)) {
            throw new Exception('Email already registered');
        }
        
        // Validate password strength
        $this->validate_password_strength($password);
        
        // Hash the password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Create user data
        $user_data = [
            'username' => $username,
            'password_hash' => $password_hash,
            'email' => $email,
            'created_at' => date('c'),
            'is_active' => true,
            'login_attempts' => 0,
            'last_attempt' => null,
            'last_login' => null
        ];
        
        // Add user to database
        return $this->users_db->add($user_data);
    }
    
    // Login user
    public function login($username, $password) {
        // Find user by username
        $user = $this->find_user_by_username($username);
        
        if (!$user) {
            throw new Exception('Invalid credentials');
        }
        
        // Check if user is active
        if (!$user['is_active']) {
            throw new Exception('Account is deactivated');
        }
        
        // Check rate limiting (max 5 attempts)
        if ($this->is_rate_limited($user)) {
            throw new Exception('Too many login attempts. Please try again in 15 minutes.');
        }
        
        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            $this->record_failed_attempt($user['id']);
            throw new Exception('Invalid credentials');
        }
        
        // Reset failed attempts on successful login
        $this->users_db->update($user['id'], [
            'last_login' => date('c'),
            'login_attempts' => 0,
            'last_attempt' => null
        ]);
        
        return $user;
    }
    
    // Find user by username
    public function find_user_by_username($username) {
        return $this->users_db->find_by_field('username', $username);
    }
    
    // Find user by email
    public function find_user_by_email($email) {
        return $this->users_db->find_by_field('email', $email);
    }
    
    // Find user by ID
    public function find_user_by_id($id) {
        return $this->users_db->find_by_id($id);
    }
    
    // Change user password
    public function change_password($user_id, $old_password, $new_password) {
        $user = $this->find_user_by_id($user_id);
        
        if (!$user) {
            throw new Exception('User not found');
        }
        
        // Verify old password
        if (!password_verify($old_password, $user['password_hash'])) {
            throw new Exception('Current password is incorrect');
        }
        
        // Validate new password strength
        $this->validate_password_strength($new_password);
        
        // Hash new password
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password
        $this->users_db->update($user_id, [
            'password_hash' => $new_hash,
            'password_changed_at' => date('c')
        ]);
        
        return true;
    }

    // Validate password strength
    public function validate_password_strength($password) {
        $errors = [];
        if (strlen($password) < 6) { $errors[] = "at least 6 characters"; }
        if (!preg_match('/[A-Z]/', $password)) { $errors[] = "at least one uppercase letter"; }
        if (!preg_match('/[a-z]/', $password)) { $errors[] = "at least one lowercase letter"; }
        if (!preg_match('/[0-9]/', $password)) { $errors[] = "at least one number"; }
        
        if (!empty($errors)) {
            throw new Exception("Password must have " . implode(', ', $errors));
        }
        
        return true;
    }
    
    // Check if user is rate limited
    private function is_rate_limited($user) {
        if (($user['login_attempts'] ?? 0) >= 5) {
            $last_attempt = strtotime($user['last_attempt'] ?? '');
            $now = time();
            $time_diff = $now - $last_attempt;
            
            // Rate limit for 15 minutes (900 seconds)
            return $time_diff < 900;
        }
        
        return false;
    }
    
    // Record failed login attempt
    private function record_failed_attempt($user_id) {
        $user = $this->users_db->find_by_id($user_id);
        
        $this->users_db->update($user_id, [
            'login_attempts' => ($user['login_attempts'] ?? 0) + 1,
            'last_attempt' => date('c')
        ]);
    }
    
    // Update user profile
    public function update_profile($user_id, $updates) {
        // Only allow safe fields to be updated
        $allowed_fields = ['email'];
        $safe_updates = [];
        
        foreach ($allowed_fields as $field) {
            if (isset($updates[$field])) {
                // Special validation for email
                if ($field === 'email') {
                    if (!filter_var($updates[$field], FILTER_VALIDATE_EMAIL)) {
                        throw new Exception('Invalid email format');
                    }
                    
                    // Check email uniqueness
                    $existing_email = $this->find_user_by_email($updates[$field]);
                    if ($existing_email && $existing_email['id'] != $user_id) {
                        throw new Exception('Email already registered to another user');
                    }
                }
                
                $safe_updates[$field] = $updates[$field];
            }
        }
        
        $safe_updates['updated_at'] = date('c');
        
        return $this->users_db->update($user_id, $safe_updates);
    }
    
    // Deactivate user
    public function deactivate_user($user_id) {
        return $this->users_db->update($user_id, [
            'is_active' => false,
            'deactivated_at' => date('c')
        ]);
    }
    
    // Activate user
    public function activate_user($user_id) {
        return $this->users_db->update($user_id, [
            'is_active' => true,
            'activated_at' => date('c')
        ]);
    }
    
    // Get all users (for admin purposes)
    public function get_all_users($include_inactive = false) {
        $users = $this->users_db->read_data();
        
        $filtered_users = [];
        foreach ($users as $user) {
            // Filter by active status
            if (!$include_inactive && !($user['is_active'] ?? true)) {
                continue;
            }
            
            // Remove sensitive data
            $safe_user = $user;
            unset($safe_user['password_hash']);
            $filtered_users[] = $safe_user;
        }
        
        return $filtered_users;
    }
    
    // Get user statistics
    public function get_user_stats() {
        $users = $this->users_db->read_data();
        
        $stats = [
            'total_users' => count($users),
            'active_users' => 0,
            'inactive_users' => 0,
            'recent_registrations' => 0, // Last 7 days
            'recent_logins' => 0 // Last 24 hours
        ];
        
        $now = time();
        $week_ago = $now - (7 * 24 * 60 * 60);
        $day_ago = $now - (24 * 60 * 60);
        
        foreach ($users as $user) {
            // Count active/inactive
            if ($user['is_active'] ?? true) {
                $stats['active_users']++;
            } else {
                $stats['inactive_users']++;
            }
            
            // Count recent registrations
            if (strtotime($user['created_at']) > $week_ago) {
                $stats['recent_registrations']++;
            }
            
            // Count recent logins
            if (isset($user['last_login']) && strtotime($user['last_login']) > $day_ago) {
                $stats['recent_logins']++;
            }
        }
        
        return $stats;
    }
}
