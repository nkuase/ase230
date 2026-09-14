<?php
require_once __DIR__ . '/../../Basics/authentication/Auth.php';
require_once __DIR__ . '/../../Basics/authentication/SessionAuth.php';

$auth = new Auth();
$session = new SessionAuth();

// Require authentication
$session->require_auth();

// Get current user
$current_user = $session->get_current_user();
$user_details = $auth->find_user_by_id($current_user['id']);

$success_message = '';
$error_message = '';

if ($_POST) {
    try {
        // Get form data
        $email = trim($_POST['email'] ?? '');
        
        // Basic validation
        if (empty($email)) {
            throw new Exception('Email is required');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }
        
        // Update profile using Auth class
        $updated_user = $auth->update_profile($current_user['id'], [
            'email' => $email
        ]);
        
        $success_message = 'Profile updated successfully!';
        
        // Refresh user details
        $user_details = $auth->find_user_by_id($current_user['id']);
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Profile</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; }
        .success { color: green; background: #eeffee; padding: 15px; margin: 15px 0; border: 1px solid green; }
        .error { color: red; background: #ffeeee; padding: 15px; margin: 15px 0; border: 1px solid red; }
        .form-group { margin: 15px 0; }
        input { padding: 8px; width: 100%; border: 1px solid #ddd; box-sizing: border-box; }
        input[readonly] { background: #f5f5f5; }
        button { padding: 10px 20px; background: #007cba; color: white; border: none; cursor: pointer; }
        .form-container { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
        .help { color: #666; font-size: 0.9em; margin-top: 5px; }
        .links { margin: 20px 0; }
        .user-info { background: #f0f8ff; padding: 15px; margin: 15px 0; }
        .readonly-info { background: #fff8e1; padding: 10px; margin: 10px 0; border-left: 4px solid orange; }
    </style>
</head>
<body>
    <h1>👤 Update Profile</h1>
    
    <div class="user-info">
        <h3>Current Profile Information</h3>
        <p><strong>Username:</strong> <?= htmlspecialchars($user_details['username']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user_details['email'] ?? 'Not set') ?></p>
        <p><strong>Member Since:</strong> <?= date('F j, Y', strtotime($user_details['created_at'])) ?></p>
        <p><strong>Last Updated:</strong> <?= isset($user_details['updated_at']) ? date('F j, Y g:i A', strtotime($user_details['updated_at'])) : 'Never' ?></p>
    </div>
    
    <?php if ($success_message): ?>
        <div class="success">
            <strong>✅ Success!</strong><br>
            <?= htmlspecialchars($success_message) ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="error">
            <strong>⚠️ Update Failed:</strong><br>
            <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>
    
    <div class="form-container">
        <h3>Edit Profile</h3>
        <form method="post">
            <div class="form-group">
                <label><strong>Username:</strong></label>
                <input type="text" value="<?= htmlspecialchars($user_details['username']) ?>" readonly>
                <div class="help">Username cannot be changed for security reasons</div>
            </div>
            
            <div class="form-group">
                <label><strong>Email:</strong> *</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user_details['email'] ?? '') ?>" required>
                <div class="help">Valid email address for account recovery</div>
            </div>
            
            <div class="form-group">
                <label><strong>User ID:</strong></label>
                <input type="text" value="<?= $user_details['id'] ?>" readonly>
                <div class="help">Unique identifier - cannot be changed</div>
            </div>
            
            <button type="submit">💾 Update Profile</button>
        </form>
    </div>
    
    <div class="readonly-info">
        <h4>⚠️ Protected Fields</h4>
        <p>For security reasons, some profile fields cannot be modified:</p>
        <ul>
            <li><strong>Username:</strong> Permanent identifier</li>
            <li><strong>User ID:</strong> System-generated unique ID</li>
            <li><strong>Password:</strong> Use <a href="change_password.php">Change Password</a> instead</li>
            <li><strong>Registration Date:</strong> Historical record</li>
        </ul>
    </div>
    
    <div class="links">
        <p><a href="change_password.php">🔑 Change Password</a></p>
        <p><a href="dashboard.php">← Back to Dashboard</a></p>
    </div>
    
    <h2>💡 Profile Security:</h2>
    <div style="background: #f0f8ff; padding: 15px; margin: 20px 0;">
        <h3>Why Some Fields Are Read-Only:</h3>
        <ul>
            <li><strong>Username Stability:</strong> Changing usernames can break references</li>
            <li><strong>Security:</strong> Prevents identity confusion and impersonation</li>
            <li><strong>Data Integrity:</strong> Maintains consistent user identification</li>
            <li><strong>Audit Trail:</strong> Preserves historical records</li>
        </ul>
        
        <h3>Email Validation:</h3>
        <ul>
            <li><strong>Format Check:</strong> Must be valid email format</li>
            <li><strong>Uniqueness:</strong> Each email can only be used once</li>
            <li><strong>Recovery:</strong> Used for password reset (if implemented)</li>
            <li><strong>Communication:</strong> For important account notifications</li>
        </ul>
    </div>
    
    <h3>🔒 Implementation Details:</h3>
    <div style="background: #eeffee; padding: 15px; margin: 15px 0; border-left: 4px solid green;">
        <h4>Safe Profile Update Process:</h4>
        <ol>
            <li><strong>Authentication Check:</strong> User must be logged in</li>
            <li><strong>Field Filtering:</strong> Only allowed fields can be updated</li>
            <li><strong>Validation:</strong> Input format and uniqueness checks</li>
            <li><strong>Database Update:</strong> Safe update with timestamp</li>
        </ol>
        
        <pre style="background: white; padding: 10px; font-family: monospace; font-size: 0.9em;">// Only allow safe fields to be updated
$allowed_fields = ['email'];
$safe_updates = [];

foreach ($allowed_fields as $field) {
    if (isset($updates[$field])) {
        $safe_updates[$field] = $updates[$field];
    }
}

// Add timestamp
$safe_updates['updated_at'] = date('c');</pre>
    </div>
    
    <h3>🧪 Test Profile Updates:</h3>
    <div style="background: #fffacd; padding: 15px; margin: 10px 0;">
        <strong>Try these test cases:</strong><br>
       - Valid email update (should succeed)<br>
       - Invalid email format like "not-an-email" (should fail)<br>
       - Email already used by another user (should fail)<br>
       - Empty email field (should fail)
    </div>
</body>
</html>
