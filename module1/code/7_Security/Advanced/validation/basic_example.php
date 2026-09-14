<?php
require_once 'Validator.php';

$validator = new Validator();

if ($_POST) {
    // Method chaining - validate multiple rules for each field
    $validator->required($_POST['name'] ?? '', 'Name')
              ->minLength($_POST['name'] ?? '', 2, 'Name')
              ->maxLength($_POST['name'] ?? '', 50, 'Name');
    
    $validator->required($_POST['email'] ?? '', 'Email')
              ->email($_POST['email'] ?? '', 'Email');
    
    $validator->required($_POST['age'] ?? '', 'Age')
              ->numeric($_POST['age'] ?? '', 'Age')
              ->min($_POST['age'] ?? 0, 18, 'Age')
              ->max($_POST['age'] ?? 0, 120, 'Age');
    
    // Process if valid
    if (!$validator->hasErrors()) {
        echo "<div style='color: green; padding: 10px; background: #eeffee; margin: 10px 0;'>
                <strong>Registration successful using Validator class!</strong><br>
                Name: " . htmlspecialchars($_POST['name']) . "<br>
                Email: " . htmlspecialchars($_POST['email']) . "<br>
                Age: " . htmlspecialchars($_POST['age']) . "<br>
                <em>Found " . $validator->count() . " errors (should be 0)</em>
              </div>";
    }
}
?>

<h1>Basic Validator Example</h1>
<p>This shows how to use our Validator class with method chaining.</p>

<form method="post" style="background: #f9f9f9; padding: 20px; border: 1px solid #ddd;">
    <h3>User Registration</h3>
    
    <div style="margin: 10px 0;">
        <label>Name (2-50 chars):</label><br>
        <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" 
               style="padding: 5px; width: 200px;">
    </div>
    
    <div style="margin: 10px 0;">
        <label>Email:</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
               style="padding: 5px; width: 200px;">
    </div>
    
    <div style="margin: 10px 0;">
        <label>Age (18-120):</label><br>
        <input type="number" name="age" value="<?= htmlspecialchars($_POST['age'] ?? '') ?>" 
               style="padding: 5px; width: 200px;">
    </div>
    
    <button type="submit" style="padding: 8px 15px; background: #007cba; color: white; border: none;">
        Register
    </button>
</form>

<?php if ($validator->hasErrors()): ?>
    <div style="color: red; background: #ffeeee; padding: 10px; margin: 10px 0; border-left: 4px solid red;">
        <strong>Found <?= $validator->count() ?> error(s):</strong>
        <?php foreach ($validator->getErrors() as $error): ?>
            <div>• <?= htmlspecialchars($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<h2>What this code does:</h2>
<div style="background: #f0f8ff; padding: 15px; margin: 10px 0;">
    <h3>1. Method Chaining Magic:</h3>
    <pre style="background: #ffffff; padding: 10px;">$validator->required($_POST['name'], 'Name')
          ->minLength($_POST['name'], 2, 'Name')
          ->maxLength($_POST['name'], 50, 'Name');</pre>
    
    <h3>2. Multiple Validations:</h3>
    <ul>
        <li><strong>Name:</strong> Required, 2-50 characters</li>
        <li><strong>Email:</strong> Required, valid email format</li>
        <li><strong>Age:</strong> Required, numeric, 18-120 range</li>
    </ul>
    
    <h3>3. Error Handling:</h3>
    <pre style="background: #ffffff; padding: 10px;">if (!$validator->hasErrors()) {
    // Process valid data
}
// Show all errors with $validator->getErrors()</pre>
</div>

<h3>🚀 Try These Test Cases:</h3>
<div style="background: #fffacd; padding: 10px; margin: 10px 0;">
    <strong>Valid:</strong> Name="John Doe", Email="john@example.com", Age=25<br>
    <strong>Invalid:</strong> Name="J" (too short), Email="invalid-email", Age=17 (too young)<br>
    <strong>Empty:</strong> Leave all fields blank to see required field errors
</div>

<p><a href="simple_validation.php">← Previous: Simple validation</a> | <a href="registration_form.php">Next: Complete form →</a></p>
