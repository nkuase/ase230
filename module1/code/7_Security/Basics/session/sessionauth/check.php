<?php
require_once 'SessionAuth.php';
$auth = new SessionAuth();
?>

<h1>Session Info</h1>

<p><strong>Session ID:</strong> <?= session_id() ?></p>

<p><strong>What's in $_SESSION:</strong></p>
<pre><?php print_r($_SESSION); ?></pre>

<?php if ($auth->is_logged_in()): ?>
    <?php $user = $auth->get_user(); ?>
    <p>Status: Logged in as <?= $user['username'] ?></p>
    <p><a href="welcome.php">Welcome Page</a></p>
    <p><a href="logout.php">Logout</a></p>
<?php else: ?>
    <p>Status: Not logged in</p>
    <p><a href="login.php">Login</a></p>
<?php endif; ?>

<p><a href="index.php">Back to Home</a></p>
