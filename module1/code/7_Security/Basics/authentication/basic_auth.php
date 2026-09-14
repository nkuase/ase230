<?php
session_start();

// Simple user database (in real app, use proper database)
$users = [
    'john' => password_hash('Secret123', PASSWORD_DEFAULT),
    'admin' => password_hash('Admin123', PASSWORD_DEFAULT)
];

// Handle login - FIX: Check if 'action' exists first
if (isset($_POST['action']) && $_POST['action'] == 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (isset($users[$username]) && password_verify($password, $users[$username])) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['login_time'] = time();
        $success_message = "Welcome, $username!";
    } else {
        $error_message = "Invalid credentials!";
    }
}

// Handle logout - FIX: Check if 'action' exists first
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header('Location: basic_auth.php');
    exit;
}
?>

<h1>Basic Authentication - Super Simple</h1>

<h2>What this demonstrates:</h2>
<ul>
    <li><strong>Password Hashing:</strong> Using password_hash() and password_verify()</li>
    <li><strong>Session Management:</strong> Login state stored in $_SESSION</li>
    <li><strong>Access Control:</strong> Different content for logged in vs logged out users</li>
</ul>

<?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
    <!-- LOGGED IN STATE -->
    <div style="background: #eeffee; padding: 15px; margin: 15px 0; border: 1px solid green;">
        <h3>✅ You are logged in as: <?= htmlspecialchars($_SESSION['username']) ?></h3>
        <p><strong>Login Time:</strong> <?= date('F j, Y g:i A', $_SESSION['login_time']) ?></p>
        <p><strong>Session ID:</strong> <?= session_id() ?></p>
        
        <h4>🔒 This is PROTECTED content only logged-in users can see!</h4>
        <ul>
            <li>Your secret account balance: $1,337,000</li>
            <li>Your private messages: 42 new messages</li>
            <li>Admin panel access: <?= $_SESSION['username'] == 'admin' ? 'GRANTED' : 'DENIED' ?></li>
        </ul>
        
        <p><a href="?action=logout" style="padding: 8px 15px; background: red; color: white; text-decoration: none;">Logout</a></p>
    </div>
    
<?php else: ?>
    <!-- LOGGED OUT STATE -->
    
    <?php if (isset($success_message)): ?>
        <div style="color: green; padding: 10px; background: #eeffee; margin: 10px 0;">
            <?= htmlspecialchars($success_message) ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div style="color: red; padding: 10px; background: #ffeeee; margin: 10px 0;">
            <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>
    
    <div style="background: #f9f9f9; padding: 15px; border: 1px solid #ddd;">
        <h3>🔓 Please login to access protected content</h3>
        
        <form method="post" style="margin: 15px 0;">
            <input type="hidden" name="action" value="login">
            
            <div style="margin: 10px 0;">
                <label>Username:</label><br>
                <input type="text" name="username" required style="padding: 5px; width: 200px;">
            </div>
            
            <div style="margin: 10px 0;">
                <label>Password:</label><br>
                <input type="password" name="password" required style="padding: 5px; width: 200px;">
            </div>
            
            <button type="submit" style="padding: 8px 15px; background: #007cba; color: white; border: none;">Login</button>
        </form>
        
        <h4>📝 Test Accounts:</h4>
        <ul>
            <li><strong>john</strong> / Secret123</li>
            <li><strong>admin</strong> / Admin123 (has admin access)</li>
        </ul>
    </div>
<?php endif; ?>

<h2>💡 How this works:</h2>
<div style="background: #f0f8ff; padding: 15px; margin: 15px 0;">
    <h3>1. Password Security:</h3>
    <pre style="background: white; padding: 10px;">// NEVER store plain passwords
$users = [
    'john' => password_hash('Secret123', PASSWORD_DEFAULT)  // ✅ SAFE
];

// Verify login
if (password_verify($input_password, $stored_hash)) {
    // Login successful
}</pre>
    
    <h3>2. Session Management:</h3>
    <pre style="background: white; padding: 10px;">session_start();  // Start session
$_SESSION['logged_in'] = true;  // Store login state
$_SESSION['username'] = $username;  // Store user info</pre>
    
    <h3>3. Access Control (FIXED - No more warnings!):</h3>
    <pre style="background: white; padding: 10px;">// ❌ OLD WAY (causes warnings):
if ($_POST['action'] == 'login') {

// ✅ NEW WAY (safe):
if (isset($_POST['action']) && $_POST['action'] == 'login') {</pre>
</div>

<h3>🔒 Security Features Demonstrated:</h3>
<ul>
    <li><strong>Password Hashing:</strong> Never store plain text passwords</li>
    <li><strong>Session Security:</strong> User state maintained securely</li>
    <li><strong>Input Validation:</strong> Check credentials AND array keys properly</li>
    <li><strong>Access Control:</strong> Content shown based on authentication</li>
</ul>

<p><a href="index.php">← Back to examples</a> | <a href="register.php">Next: User registration →</a></p>