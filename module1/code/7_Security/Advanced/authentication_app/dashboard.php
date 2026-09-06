<?php
require_once __DIR__ . '/../../Basics/authentication/Auth.php';
require_once __DIR__ . '/../../Basics/authentication/SessionAuth.php';

$auth = new Auth();
$session = new SessionAuth();

// Require authentication
$session->require_auth();

// Get current user info
$current_user = $session->get_current_user();
$user_details = $auth->find_user_by_id($current_user['id']);
$session_info = $session->get_session_info();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .user-info { background: #f0f0f0; padding: 15px; margin: 15px 0; border: 1px solid #ddd; }
        .actions { margin: 20px 0; }
        .actions a { 
            display: inline-block; 
            margin: 5px 10px 5px 0; 
            padding: 8px 15px; 
            background: #007cba; 
            color: white; 
            text-decoration: none; 
            border-radius: 3px;
        }
        .actions a:hover { background: #005a8b; }
        .logout { background: #dc3545; }
        .logout:hover { background: #c82333; }
        .stats { background: #f8f9fa; padding: 15px; margin: 15px 0; }
        .protected { background: #d4edda; padding: 15px; margin: 15px 0; border: 1px solid #28a745; }
    </style>
</head>
<body>
    <h1>🏠 Dashboard</h1>
    
    <div class="user-info">
        <h3>Welcome back, <?= htmlspecialchars($current_user['username']) ?>! 👋</h3>
        <p><strong>User ID:</strong> <?= $current_user['id'] ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user_details['email'] ?? 'Not provided') ?></p>
        <p><strong>Member since:</strong> <?= date('F j, Y', strtotime($user_details['created_at'])) ?></p>
        <p><strong>Last login:</strong> <?= date('F j, Y g:i A', $current_user['login_time']) ?></p>
    </div>
    
    <div class="actions">
        <a href="change_password.php">🔑 Change Password</a>
        <a href="update_profile.php">👤 Update Profile</a>
        <a href="user_management.php">👥 User Management</a>
        <a href="logout.php" class="logout">🚪 Logout</a>
    </div>
    
    <div class="protected">
        <h3>🔒 This is PROTECTED Content!</h3>
        <p>Only authenticated users can see this information:</p>
        <ul>
            <li><strong>Secret Balance:</strong> $<?= number_format(rand(10000, 999999)) ?></li>
            <li><strong>Private Messages:</strong> <?= rand(1, 50) ?> unread</li>
            <li><strong>Admin Access:</strong> <?= $current_user['username'] === 'admin' ? '✅ GRANTED' : '❌ DENIED' ?></li>
            <li><strong>VIP Status:</strong> <?= rand(0, 1) ? '⭐ ACTIVE' : '📋 STANDARD' ?></li>
        </ul>
    </div>
    
    <div class="stats">
        <h3>📊 Your Account Statistics:</h3>
        <ul>
            <li><strong>Account Status:</strong> <?= $user_details['is_active'] ? '✅ Active' : '❌ Inactive' ?></li>
            <li><strong>Failed Login Attempts:</strong> <?= $user_details['login_attempts'] ?? 0 ?></li>
            <li><strong>Profile Last Updated:</strong> <?= isset($user_details['updated_at']) ? date('F j, Y', strtotime($user_details['updated_at'])) : 'Never' ?></li>
            <li><strong>Password Last Changed:</strong> <?= isset($user_details['password_changed_at']) ? date('F j, Y', strtotime($user_details['password_changed_at'])) : 'Never' ?></li>
        </ul>
    </div>
    
    <h3>🔧 Session Information (for debugging):</h3>
    <div style="background: #f8f9fa; padding: 15px; margin: 15px 0; font-family: monospace; font-size: 0.9em;">
        <strong>Session ID:</strong> <?= $session_info['session_id'] ?><br>
        <strong>Logged In:</strong> <?= $session_info['logged_in'] ? 'Yes' : 'No' ?><br>
        <strong>Username:</strong> <?= $session_info['username'] ?><br>
        <strong>Login Time:</strong> <?= date('Y-m-d H:i:s', $session_info['login_time']) ?><br>
        <strong>Session Age:</strong> <?= gmdate('H:i:s', $session_info['session_age']) ?> (HH:MM:SS)<br>
        <strong>Timeout:</strong> Session expires after 30 minutes of inactivity
    </div>
    
    <h2>💡 What This Example Shows:</h2>
    <div style="background: #f0f8ff; padding: 15px; margin: 20px 0;">
        <h3>Authentication Features:</h3>
        <ul>
            <li><strong>Access Control:</strong> Only logged-in users can see this page</li>
            <li><strong>Session Management:</strong> User info maintained across requests</li>
            <li><strong>User Data Display:</strong> Safe display of user information</li>
            <li><strong>Session Timeout:</strong> Automatic logout after 30 minutes</li>
        </ul>
        
        <h3>Security Implementation:</h3>
        <ul>
            <li><strong>require_auth():</strong> Automatically redirects if not logged in</li>
            <li><strong>Session Regeneration:</strong> New session ID on login prevents fixation</li>
            <li><strong>Data Sanitization:</strong> htmlspecialchars() prevents XSS</li>
            <li><strong>Timeout Protection:</strong> Sessions expire for security</li>
        </ul>
    </div>
    
    <h3>🧪 Try These Actions:</h3>
    <div style="background: #fffacd; padding: 15px; margin: 10px 0;">
        <ul>
            <li><strong>Wait 30+ minutes:</strong> Session should timeout and redirect to login</li>
            <li><strong>Open new tab:</strong> Try accessing dashboard.php directly - should work</li>
            <li><strong>Clear cookies:</strong> Then refresh - should redirect to login</li>
            <li><strong>Test other features:</strong> Change password, update profile, etc.</li>
        </ul>
    </div>
    
    <p><a href="index.php">← Back to main menu</a></p>
</body>
</html>
