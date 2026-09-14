<?php
session_start();
?>

<h1>Session Info</h1>

<p><strong>Session ID:</strong> <?= session_id() ?></p>

<p><strong>What's in $_SESSION:</strong></p>
<pre><?php print_r($_SESSION); ?></pre>

<?php if ($_SESSION['logged_in']): ?>
    <p>Status: Logged in as <?= $_SESSION['username'] ?></p>
    <p><a href="welcome.php">Welcome Page</a></p>
    <p><a href="logout.php">Logout</a></p>
<?php else: ?>
    <p>Status: Not logged in</p>
    <p><a href="login.php">Login</a></p>
<?php endif; ?>
