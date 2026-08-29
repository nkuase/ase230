<?php
require_once 'SessionAuth.php';
$auth = new SessionAuth();

if ($_POST && $_POST['username'] == 'john' && $_POST['password'] == 'Secret123') {
    $user_data = [
        'id' => 1,
        'username' => 'john'
    ];
    $auth->login_user($user_data);
    header('Location: welcome.php');
    exit;
}
?>

<h1>Login</h1>

<?php if ($_POST && ($_POST['username'] != 'john' || $_POST['password'] != 'secret')): ?>
    <p style="color:red">Wrong username or password!</p>
<?php endif; ?>

<form method="post">
    Username: <input type="text" name="username"><br><br>
    Password: <input type="password" name="password"><br><br>
    <input type="submit" value="Login">
</form>

<p>Use: john / Secret123</p>
<p><a href="check.php">Check Session</a> | <a href="index.php">Back to Home</a></p>
