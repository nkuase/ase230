<?php
require_once 'SessionAuth.php';
$auth = new SessionAuth();
?>

<h1>Session Demo - SessionAuth Class</h1>

<h2>What are sessions?</h2>
<p>Sessions let PHP remember information about you across different pages.</p>

<h2>How it works:</h2>
<ol>
    <li>SessionAuth class handles session_start() automatically</li>
    <li>$auth->login_user($user) - stores your data securely</li>
    <li>PHP remembers this data on other pages</li>
</ol>

<h2>Try it:</h2>
<p><a href="login.php">1. Login (john/secret)</a></p>
<p><a href="welcome.php">2. Welcome Page (protected)</a></p>
<p><a href="check.php">3. Check Session Data</a></p>
<p><a href="logout.php">4. Logout</a></p>

<?php if ($auth->is_logged_in()): ?>
    <?php $user = $auth->get_user(); ?>
    <h3>Current Status: Logged in as <?= $user['username'] ?> (ID: <?= $user['id'] ?>)</h3>
<?php else: ?>
    <h3>Current Status: Not logged in</h3>
<?php endif; ?>
