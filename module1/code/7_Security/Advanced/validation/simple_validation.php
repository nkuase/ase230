<?php
$errors = [];

if ($_POST) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $age = $_POST['age'] ?? '';
    
    // Check required fields
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    if (empty($email)) {
        $errors[] = "Email is required";
    }
    
    // Check email format
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Check age
    if (!empty($age) && (!is_numeric($age) || $age < 18)) {
        $errors[] = "Age must be at least 18";
    }
    
    // If no errors, process data
    if (empty($errors)) {
        echo "<div style='color: green; padding: 10px; background: #eeffee; margin: 10px 0;'>
                <strong>Registration successful!</strong><br>
                Name: " . htmlspecialchars($name) . "<br>
                Email: " . htmlspecialchars($email) . "<br>
                Age: " . htmlspecialchars($age) . "
              </div>";
    }
}
?>

<h1>Simple Validation (without class)</h1>
<p>This shows basic validation using plain PHP without a validator class.</p>

<form method="post" style="background: #f9f9f9; padding: 20px; border: 1px solid #ddd;">
    <h3>Registration Form</h3>
    
    <div style="margin: 10px 0;">
        <label>Name:</label><br>
        <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" 
               style="padding: 5px; width: 200px;">
    </div>
    
    <div style="margin: 10px 0;">
        <label>Email:</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
               style="padding: 5px; width: 200px;">
    </div>
    
    <div style="margin: 10px 0;">
        <label>Age:</label><br>
        <input type="number" name="age" value="<?= htmlspecialchars($_POST['age'] ?? '') ?>" 
               style="padding: 5px; width: 200px;">
    </div>
    
    <button type="submit" style="padding: 8px 15px; background: #007cba; color: white; border: none;">
        Register
    </button>
</form>

<?php if (!empty($errors)): ?>
    <div style="color: red; background: #ffeeee; padding: 10px; margin: 10px 0; border-left: 4px solid red;">
        <strong>Please fix the following errors:</strong>
        <?php foreach ($errors as $error): ?>
            <div>• <?= htmlspecialchars($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<h2>What this code does:</h2>
<ol>
    <li><strong>Collects data:</strong> $_POST['name'], $_POST['email'], $_POST['age']</li>
    <li><strong>Checks required fields:</strong> Uses empty() function</li>
    <li><strong>Validates email:</strong> Uses filter_var() with FILTER_VALIDATE_EMAIL</li>
    <li><strong>Validates age:</strong> Checks if numeric and >= 18</li>
    <li><strong>Shows errors:</strong> Displays all validation errors to user</li>
</ol>

<h3>💡 Key Concepts:</h3>
<div style="background: #f0f8ff; padding: 10px; margin: 10px 0;">
    <strong>htmlspecialchars():</strong> Prevents XSS attacks by escaping HTML<br>
    <strong>filter_var():</strong> PHP's built-in validation functions<br>
    <strong>empty():</strong> Checks if value is empty (null, '', 0, false, etc.)
</div>

<p><a href="index.php">← Back to examples</a> | <a href="basic_example.php">Next: Using Validator class →</a></p>
