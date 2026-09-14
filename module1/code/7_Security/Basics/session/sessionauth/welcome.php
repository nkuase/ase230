<?php
require_once 'SessionAuth.php';
$session = new SessionAuth();

// This replaces the manual authentication check
$session->require_auth();

$user = $session->get_user();
?>

<h1>Welcome!</h1>

<p>Hello, <?= $user['username'] ?>!</p>
<p>Your user ID is: <?= $user['id'] ?></p>
<p>You are logged in.</p>

<p><a href="logout.php">Logout</a></p>
<p><a href="check.php">Check Session</a> | <a href="index.php">Back to Home</a></p>
