<?php
session_start();

if ($_POST['username'] == 'john' && $_POST['password'] == 'Secret123') {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'john';
    $_SESSION['logged_in'] = true;
    header('Location: welcome.php');
    exit;
}
?>

<h1>Login</h1>

<?php if ($_POST && ($_POST['username'] != 'john' || $_POST['password'] != 'Secret123')): ?>
    <p style="color:red">Wrong username or password!</p>
<?php endif; ?>

<form method="post">
    Username: <input type="text" name="username"><br><br>
    Password: <input type="password" name="password"><br><br>
    <input type="submit" value="Login">
</form>

<p>Use: john / Secret123</p>
<p><a href="check.php">Check Session</a> | <a href="basic.php">Basic Example</a></p>
