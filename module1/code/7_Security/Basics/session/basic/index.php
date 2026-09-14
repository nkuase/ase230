<?php
session_start();
?>

<h1>Session Demo - Super Simple</h1>

<h2>What are sessions?</h2>
<p>Sessions let PHP remember information about you across different pages.</p>

<h2>How it works:</h2>
<ol>
    <li>session_start() - tells PHP to start tracking you</li>
    <li>$_SESSION['data'] = 'value' - stores your data</li>
    <li>PHP remembers this data on other pages</li>
</ol>

<h2>Try it:</h2>
<p><a href="login.php">1. Login (john/Secret123)</a></p>
<p><a href="welcome.php">2. Welcome Page (protected)</a></p>
<p><a href="basic.php">3. Basic Example (from slides)</a></p>
<p><a href="check.php">4. Check Session Data</a></p>
<p><a href="logout.php">5. Logout</a></p>

<?php if ($_SESSION['logged_in']): ?>
    <h3>Current Status: Logged in as <?= $_SESSION['username'] ?> (ID: <?= $_SESSION['user_id'] ?>)</h3>
<?php else: ?>
    <h3>Current Status: Not logged in</h3>
<?php endif; ?>
