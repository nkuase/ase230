<?php
require_once 'Validator.php';

$validator = new Validator();

if ($_POST) {
    // Validate all fields with method chaining
    $validator->required($_POST['username'] ?? '', 'Username')
              ->username($_POST['username'] ?? '');
    
    $validator->required($_POST['email'] ?? '', 'Email')
              ->email($_POST['email'] ?? '');
    
    $validator->required($_POST['password'] ?? '', 'Password')
              ->minLength($_POST['password'] ?? '', 6, 'Password')
              ->maxLength($_POST['password'] ?? '', 50, 'Password');
    
    $validator->required($_POST['age'] ?? '', 'Age')
              ->numeric($_POST['age'] ?? '', 'Age')
              ->min($_POST['age'] ?? 0, 18, 'Age')
              ->max($_POST['age'] ?? 0, 120, 'Age');
    
    // Optional field validation
    if (!empty($_POST['website'])) {
        $validator->pattern($_POST['website'], '/^https?:\/\/.+/', 'Website', 
                           'Website must start with http:// or https://');
    }
    
    if (!empty($_POST['phone'])) {
        $validator->pattern($_POST['phone'], '/^[\d\s\-\(\)\.]{10,15}$/', 'Phone',
                           'Phone must be 10-15 digits with spaces, dashes, dots, or parentheses');
    }
    
    // Process if valid
    if (!$validator->hasErrors()) {
        echo "<div style='color: green; padding: 15px; background: #eeffee; margin: 10px 0; border: 1px solid green;'>
                <h3>✅ Registration Successful!</h3>
                <strong>User Data:</strong><br>
                Username: " . htmlspecialchars($_POST['username']) . "<br>
                Email: " . htmlspecialchars($_POST['email']) . "<br>
                Age: " . htmlspecialchars($_POST['age']) . "<br>
                Website: " . htmlspecialchars($_POST['website'] ?? 'Not provided') . "<br>
                Phone: " . htmlspecialchars($_POST['phone'] ?? 'Not provided') . "<br>
                <em>In a real app, this data would be saved to database with prepared statements!</em>
              </div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Complete Registration Form</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .error { color: red; margin: 5px 0; background: #ffeeee; padding: 10px; border-left: 4px solid red; }
        .form-group { margin: 15px 0; }
        input { padding: 8px; width: 300px; border: 1px solid #ddd; }
        button { padding: 10px 20px; background: #007cba; color: white; border: none; cursor: pointer; }
        button:hover { background: #005a8b; }
        .form-container { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
        .help { color: #666; font-size: 0.9em; }
    </style>
</head>
<body>
    <h1>Complete Registration Form</h1>
    <p>This form demonstrates comprehensive validation with our Validator class.</p>
    
    <?php if ($validator->hasErrors()): ?>
        <div class="error">
            <strong>⚠️ Please fix the following <?= $validator->count() ?> error(s):</strong><br>
            <?php foreach ($validator->getErrors() as $error): ?>
                <div>• <?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div class="form-container">
        <form method="post">
            <div class="form-group">
                <label><strong>Username:</strong> *</label><br>
                <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                <div class="help">3-20 characters, letters, numbers, underscore, or hyphen only</div>
            </div>
            
            <div class="form-group">
                <label><strong>Email:</strong> *</label><br>
                <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                <div class="help">Must be a valid email address</div>
            </div>
            
            <div class="form-group">
                <label><strong>Password:</strong> *</label><br>
                <input type="password" name="password" required>
                <div class="help">6-50 characters</div>
            </div>
            
            <div class="form-group">
                <label><strong>Age:</strong> *</label><br>
                <input type="number" name="age" value="<?= htmlspecialchars($_POST['age'] ?? '') ?>" required>
                <div class="help">Must be between 18 and 120</div>
            </div>
            
            <div class="form-group">
                <label><strong>Website:</strong> (optional)</label><br>
                <input type="url" name="website" value="<?= htmlspecialchars($_POST['website'] ?? '') ?>">
                <div class="help">Must start with http:// or https://</div>
            </div>
            
            <div class="form-group">
                <label><strong>Phone:</strong> (optional)</label><br>
                <input type="tel" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                <div class="help">10-15 digits, spaces and punctuation allowed</div>
            </div>
            
            <button type="submit">🚀 Register User</button>
        </form>
    </div>
    
    <h2>💡 What This Example Teaches:</h2>
    <div style="background: #f0f8ff; padding: 15px; margin: 20px 0;">
        <h3>Validation Rules Applied:</h3>
        <ul>
            <li><strong>Required fields:</strong> Username, email, password, age</li>
            <li><strong>Format validation:</strong> Email format, username pattern</li>
            <li><strong>Length constraints:</strong> Password 6-50 chars, username 3-20 chars</li>
            <li><strong>Range validation:</strong> Age between 18-120</li>
            <li><strong>Pattern matching:</strong> Website URL format, phone number format</li>
            <li><strong>Optional fields:</strong> Website and phone (validated only if provided)</li>
        </ul>
        
        <h3>Security Features:</h3>
        <ul>
            <li><strong>htmlspecialchars():</strong> Prevents XSS in form redisplay</li>
            <li><strong>Server-side validation:</strong> Never trust client-side only</li>
            <li><strong>Method chaining:</strong> Clean, readable validation code</li>
            <li><strong>Comprehensive error reporting:</strong> All errors shown at once</li>
        </ul>
    </div>
    
    <h3>🧪 Test Cases to Try:</h3>
    <div style="background: #fffacd; padding: 15px; margin: 10px 0;">
        <strong>Valid Data:</strong><br>
        Username: john_doe, Email: john@example.com, Password: mypass123, Age: 25<br>
        Website: https://example.com, Phone: 123-456-7890<br><br>
        
        <strong>Invalid Data:</strong><br>
        Username: xy (too short), Email: bad-email, Password: 123 (too short), Age: 17<br>
        Website: example.com (missing protocol), Phone: abc123 (invalid format)
    </div>
    
    <p>
        <a href="basic_example.php">← Previous: Basic example</a> | 
        <a href="api_validation.php">Next: API validation →</a>
    </p>
</body>
</html>
