<?php
session_start();

if (!$_SESSION['logged_in']) {
    header('Location: login.php');
    exit;
}
?>

<h1>Welcome!</h1>

<p>Hello, <?= $_SESSION['username'] ?>!</p>
<p>Your user ID is: <?= $_SESSION['user_id'] ?></p>
<p>You are logged in.</p>

<p><a href="logout.php">Logout</a></p>
<p><a href="check.php">Check Session</a> | <a href="basic.php">Basic Example</a></p>
