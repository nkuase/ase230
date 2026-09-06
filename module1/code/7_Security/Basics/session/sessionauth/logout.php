<?php
require_once 'SessionAuth.php';
$auth = new SessionAuth();
$auth->logout_user();
?>

<h1>Logged Out</h1>

<p>You are now logged out.</p>
<p>Your session has been destroyed.</p>

<p><a href="login.php">Login Again</a></p>
<p><a href="welcome.php">Try Welcome Page (should fail)</a></p>
