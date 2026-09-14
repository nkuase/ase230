<?php
require_once __DIR__ . '/../../Basics/authentication/Auth.php';
require_once __DIR__ . '/../../Basics/authentication/SessionAuth.php';

$auth = new Auth();
$session = new SessionAuth();

// Check if already logged in
if ($session->is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error_message = '';
$timeout_message = '';

// Check for session timeout
if (isset($_GET['timeout'])) {
    $timeout_message = 'Your session has expired. Please login again.';
}

if ($_POST) {
    try {
        // Get form data
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Basic validation
        if (empty($username) || empty($password)) {
            throw new Exception('Username and password are required');
        }
        
        // Attempt login with Auth class
        $user = $auth->login($username, $password);
        
        // Login successful - create session
        $session->login_user($user);
        
        // Redirect to dashboard
        header('Location: dashboard.php');
        exit;
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Login</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px; }
        .error { color: red; background: #ffeeee; padding: 15px; margin: 15px 0; border: 1px solid red; }
        .warning { color: orange; background: #fff8e1; padding: 15px; margin: 15px 0; border: 1px solid orange; }
        .form-group { margin: 15px 0; }
        input { padding: 8px; width: 100%; border: 1px solid #ddd; box-sizing: border-box; }
        button { padding: 10px 20px; background: #007cba; color: white; border: none; cursor: pointer; width: 100%; }
        .form-container { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
        .links { margin: 20px 0; text-align: center; }
        .demo-accounts { background: #f0f8ff; padding: 15px; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>Login</h1>
    <p>Enter your credentials to access your account.</p>
    
    <?php if ($timeout_message): ?>
        <div class="warning">
            <strong>⏰ Session Timeout:</strong><br>
            <?= htmlspecialchars($timeout_message) ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="error">
            <strong>⚠️ Login Failed:</strong><br>
            <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>
    
    <div class="form-container">
        <form method="post">
            <div class="form-group">
                <label><strong>Username:</strong></label>
                <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label><strong>Password:</strong></label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit">🔑 Login</button>
        </form>
    </div>
    
    <div class="links">
        <p><a href="register.php">Don't have an account? Register here</a></p>
        <p><a href="index.php">← Back to main menu</a></p>
    </div>
    
    <div class="demo-accounts">
        <h3>🧪 Demo Accounts (for testing):</h3>
        <p>If you don't want to register, you can use these test accounts:</p>
        <ul>
            <li><strong>Username:</strong> john <strong>Password:</strong> Secret123</li>
            <li>Or create an account using the <a href="register.php">registration form</a></li>
        </ul>
        <p><em>Note: Demo accounts are created automatically if they don't exist.</em></p>
    </div>
    
    <h2>💡 What This Example Teaches:</h2>
    <div style="background: #f0f8ff; padding: 15px; margin: 20px 0;">
        <h3>Login Security:</h3>
        <ul>
            <li><strong>Password Verification:</strong> Using password_verify() to check hashed passwords</li>
            <li><strong>Rate Limiting:</strong> Prevents brute force attacks (5 attempts max)</li>
            <li><strong>Session Creation:</strong> Secure session management after login</li>
            <li><strong>User Feedback:</strong> Clear error messages without revealing too much</li>
        </ul>
        
        <h3>Authentication Flow:</h3>
        <ol>
            <li>User submits credentials</li>
            <li>System validates input</li>
            <li>Auth class verifies username/password</li>
            <li>SessionAuth creates secure session</li>
            <li>User redirected to protected area</li>
        </ol>
    </div>
    
    <h3>🔒 Security Features:</h3>
    <div style="background: #eeffee; padding: 15px; margin: 10px 0; border-left: 4px solid green;">
        <ul>
            <li><strong>Rate Limiting:</strong> Max 5 login attempts per 15 minutes</li>
            <li><strong>Session Security:</strong> Session ID regenerated on login</li>
            <li><strong>Account Status:</strong> Checks if account is active</li>
            <li><strong>Input Validation:</strong> Prevents empty or malicious input</li>
        </ul>
    </div>
</body>
</html>

<?php
// Create demo account if it doesn't exist (for testing purposes)
try {
    if (!$auth->find_user_by_username('john')) {
        $auth->register('john', 'Secret123', 'john@example.com');
    }
} catch (Exception $e) {
    // Demo account already exists or other error - ignore
}
?>
