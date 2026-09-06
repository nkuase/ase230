<?php
require_once __DIR__ . '/../../Basics/authentication/SessionAuth.php';

$session = new SessionAuth();

// Check if user was logged in
$was_logged_in = $session->is_logged_in();
$username = $session->get_current_user()['username'] ?? 'User';

// Logout user
$session->logout();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Logged Out</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px; text-align: center; }
        .success { color: green; background: #eeffee; padding: 20px; margin: 20px 0; border: 1px solid green; }
        .info { background: #f0f8ff; padding: 15px; margin: 15px 0; }
        .links { margin: 30px 0; }
        .links a { 
            display: inline-block; 
            margin: 10px; 
            padding: 10px 20px; 
            background: #007cba; 
            color: white; 
            text-decoration: none; 
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <?php if ($was_logged_in): ?>
        <div class="success">
            <h2>✅ Logout Successful</h2>
            <p>Goodbye, <?= htmlspecialchars($username) ?>! You have been safely logged out.</p>
        </div>
    <?php else: ?>
        <div class="info">
            <h2>ℹ️ Already Logged Out</h2>
            <p>You are not currently logged in.</p>
        </div>
    <?php endif; ?>
    
    <h3>🔒 What Just Happened:</h3>
    <div class="info">
        <ul style="text-align: left;">
            <li><strong>Session Destroyed:</strong> All session data cleared</li>
            <li><strong>Session Cookie Deleted:</strong> Browser cookie removed</li>
            <li><strong>Security:</strong> You are now completely logged out</li>
        </ul>
    </div>
    
    <div class="links">
        <a href="login.php">🔑 Login Again</a>
        <a href="register.php">📝 Register New Account</a>
        <a href="index.php">🏠 Main Menu</a>
    </div>
    
    <h3>💡 Logout Security:</h3>
    <div style="background: #f0f8ff; padding: 15px; margin: 20px 0; text-align: left;">
        <p><strong>Proper logout is important for security:</strong></p>
        <ul>
            <li><strong>Session Destruction:</strong> Prevents session hijacking</li>
            <li><strong>Cookie Removal:</strong> Clears browser session cookie</li>
            <li><strong>Redirect:</strong> Prevents back button access to protected pages</li>
            <li><strong>Complete Cleanup:</strong> All traces of login removed</li>
        </ul>
        
        <h4>Logout Implementation:</h4>
        <pre style="background: white; padding: 10px; font-family: monospace;">session_start();
$_SESSION = [];                    // Clear session data
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');  // Delete cookie
}
session_destroy();               // Destroy session</pre>
    </div>
    
    <p><em>Always logout when using shared computers!</em></p>
</body>
</html>
