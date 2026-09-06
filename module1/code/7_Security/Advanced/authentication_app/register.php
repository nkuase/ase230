<?php
require_once __DIR__ . '/../../Basics/authentication/Auth.php';
require_once __DIR__ . '/../../Basics/authentication/SessionAuth.php';

$auth = new Auth();
$session = new SessionAuth();

// Redirect if already logged in
$session->require_guest();

$success_message = '';
$error_message = '';

if ($_POST) {
    try {
        // Get form data
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Basic validation
        if (empty($username) || empty($email) || empty($password)) {
            throw new Exception('All fields are required');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }
        
        if ($password !== $confirm_password) {
            throw new Exception('Passwords do not match');
        }
        
        // Register user with Auth class
        $user = $auth->register($username, $password, $email);
        
        $success_message = "Registration successful! User ID: {$user['id']}. You can now <a href='login.php'>login</a>.";
        
        // Clear form on success
        $_POST = [];
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; }
        .success { color: green; background: #eeffee; padding: 15px; margin: 15px 0; border: 1px solid green; }
        .error { color: red; background: #ffeeee; padding: 15px; margin: 15px 0; border: 1px solid red; }
        .form-group { margin: 15px 0; }
        input { padding: 8px; width: 100%; border: 1px solid #ddd; box-sizing: border-box; }
        button { padding: 10px 20px; background: #007cba; color: white; border: none; cursor: pointer; }
        .form-container { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
        .help { color: #666; font-size: 0.9em; margin-top: 5px; }
        .links { margin: 20px 0; }
    </style>
</head>
<body>
    <h1>User Registration</h1>
    <p>Create a new account to access the authentication system.</p>
    
    <?php if ($success_message): ?>
        <div class="success">
            <strong>✅ Success!</strong><br>
            <?= $success_message ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="error">
            <strong>⚠️ Registration Failed:</strong><br>
            <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>
    
    <div class="form-container">
        <form method="post">
            <div class="form-group">
                <label><strong>Username:</strong> *</label>
                <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                <div class="help">Choose a unique username (3-20 characters)</div>
            </div>
            
            <div class="form-group">
                <label><strong>Email:</strong> *</label>
                <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                <div class="help">We'll use this for account recovery</div>
            </div>
            
            <div class="form-group">
                <label><strong>Password:</strong> *</label>
                <input type="password" name="password" required>
                <div class="help">At least 6 characters with uppercase, lowercase, and number</div>
            </div>
            
            <div class="form-group">
                <label><strong>Confirm Password:</strong> *</label>
                <input type="password" name="confirm_password" required>
                <div class="help">Must match the password above</div>
            </div>
            
            <button type="submit">🚀 Create Account</button>
        </form>
    </div>
    
    <div class="links">
        <p><a href="login.php">Already have an account? Login here</a></p>
        <p><a href="index.php">← Back to main menu</a></p>
    </div>
    
    <h2>💡 What This Example Teaches:</h2>
    <div style="background: #f0f8ff; padding: 15px; margin: 20px 0;">
        <h3>Registration Security:</h3>
        <ul>
            <li><strong>Password Hashing:</strong> Never store plain passwords</li>
            <li><strong>Input Validation:</strong> Check required fields and formats</li>
            <li><strong>Duplicate Prevention:</strong> Username and email uniqueness</li>
            <li><strong>Password Strength:</strong> Enforce strong password policies</li>
        </ul>
        
        <h3>Code Features:</h3>
        <ul>
            <li><strong>Auth Class Integration:</strong> Uses our reusable Auth class</li>
            <li><strong>Error Handling:</strong> Try/catch for graceful error messages</li>
            <li><strong>Form Security:</strong> XSS prevention with htmlspecialchars()</li>
            <li><strong>User Feedback:</strong> Clear success and error messages</li>
        </ul>
    </div>
    
    <h3>🧪 Try These Test Cases:</h3>
    <div style="background: #fffacd; padding: 15px; margin: 10px 0;">
        <strong>Valid Registration:</strong><br>
        Username: testuser, Email: test@example.com, Password: MyPass123<br><br>
        
        <strong>Test Validations:</strong><br>
       - Try duplicate username (should fail)<br>
       - Try weak password like "123" (should fail)<br>
       - Try non-matching password confirmation (should fail)<br>
       - Try invalid email format (should fail)
    </div>
</body>
</html>
