<?php
require_once 'Validator.php';

// Extended Validator with custom validation methods
class CustomValidator extends Validator {
    
    // Strong password validation
    public function strongPassword($value, $field_name) {
        if (empty($value)) return $this;
        
        $errors = [];
        
        if (strlen($value) < 8) {
            $errors[] = "at least 8 characters";
        }
        if (!preg_match('/[A-Z]/', $value)) {
            $errors[] = "at least one uppercase letter";
        }
        if (!preg_match('/[a-z]/', $value)) {
            $errors[] = "at least one lowercase letter";
        }
        if (!preg_match('/[0-9]/', $value)) {
            $errors[] = "at least one number";
        }
        if (!preg_match('/[!@#$%^&*]/', $value)) {
            $errors[] = "at least one special character (!@#$%^&*)";
        }
        
        if (!empty($errors)) {
            $this->errors[] = "$field_name must have " . implode(', ', $errors);
        }
        
        return $this;
    }
    
    // Phone number validation
    public function phone($value, $field_name) {
        if (!empty($value)) {
            // Allow various formats: (123) 456-7890, 123-456-7890, 123.456.7890
            $pattern = '/^[\+]?[1-9]?[\d\s\-\(\)\.]{10,15}$/';
            if (!preg_match($pattern, $value)) {
                $this->errors[] = "$field_name must be a valid phone number";
            }
        }
        return $this;
    }
    
    // Credit card number validation (basic)
    public function creditCard($value, $field_name) {
        if (!empty($value)) {
            // Remove spaces and dashes
            $number = preg_replace('/[\s-]/', '', $value);
            
            // Check if all digits and proper length
            if (!preg_match('/^\d{13,19}$/', $number)) {
                $this->errors[] = "$field_name must be a valid credit card number";
            }
        }
        return $this;
    }
    
    // URL validation
    public function url($value, $field_name) {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->errors[] = "$field_name must be a valid URL";
        }
        return $this;
    }
    
    // Date validation
    public function date($value, $field_name, $format = 'Y-m-d') {
        if (!empty($value)) {
            $d = DateTime::createFromFormat($format, $value);
            if (!$d || $d->format($format) !== $value) {
                $this->errors[] = "$field_name must be a valid date in format $format";
            }
        }
        return $this;
    }
    
    // Password confirmation validation
    public function passwordConfirm($password, $confirm, $field_name = 'Password confirmation') {
        if ($password !== $confirm) {
            $this->errors[] = "$field_name must match the password";
        }
        return $this;
    }
    
    // Custom business rule: age appropriate for license type
    public function licenseAge($age, $license_type, $field_name = 'Age') {
        $min_ages = [
            'learner' => 15,
            'motorcycle' => 16,
            'car' => 18,
            'truck' => 21,
            'commercial' => 25
        ];
        
        if (isset($min_ages[$license_type]) && $age < $min_ages[$license_type]) {
            $this->errors[] = "$field_name must be at least {$min_ages[$license_type]} for $license_type license";
        }
        
        return $this;
    }
}

$validator = new CustomValidator();

if ($_POST) {
    // Basic info validation
    $validator->required($_POST['username'] ?? '', 'Username')
              ->minLength($_POST['username'] ?? '', 3, 'Username')
              ->maxLength($_POST['username'] ?? '', 20, 'Username')
              ->pattern($_POST['username'] ?? '', '/^[a-zA-Z0-9_-]+$/', 'Username', 
                       'Username can only contain letters, numbers, underscore and hyphen');
    
    $validator->required($_POST['email'] ?? '', 'Email')
              ->email($_POST['email'] ?? '', 'Email');
    
    // Strong password validation
    $validator->required($_POST['password'] ?? '', 'Password')
              ->strongPassword($_POST['password'] ?? '', 'Password');
    
    $validator->passwordConfirm($_POST['password'] ?? '', $_POST['confirm_password'] ?? '');
    
    // Phone validation (optional)
    if (!empty($_POST['phone'])) {
        $validator->phone($_POST['phone'], 'Phone');
    }
    
    // Date validation
    $validator->required($_POST['birth_date'] ?? '', 'Birth Date')
              ->date($_POST['birth_date'] ?? '', 'Birth Date');
    
    // URL validation (optional)
    if (!empty($_POST['website'])) {
        $validator->url($_POST['website'], 'Website');
    }
    
    // Credit card validation (optional)
    if (!empty($_POST['credit_card'])) {
        $validator->creditCard($_POST['credit_card'], 'Credit Card');
    }
    
    // Custom business logic: age and license validation
    $age = $_POST['age'] ?? '';
    $license_type = $_POST['license_type'] ?? '';
    
    $validator->required($age, 'Age')
              ->numeric($age, 'Age')
              ->min($age, 1, 'Age')
              ->max($age, 120, 'Age');
    
    if (!empty($age) && !empty($license_type)) {
        $validator->licenseAge((int)$age, $license_type);
    }
    
    // Process if valid
    if (!$validator->hasErrors()) {
        echo "<div style='color: green; padding: 15px; background: #eeffee; margin: 10px 0; border: 1px solid green;'>
                <h3>✅ All Custom Validations Passed!</h3>
                <strong>Validated Data:</strong><br>
                Username: " . htmlspecialchars($_POST['username']) . "<br>
                Email: " . htmlspecialchars($_POST['email']) . "<br>
                Password: " . str_repeat('*', strlen($_POST['password'])) . " (strong password ✓)<br>
                Phone: " . htmlspecialchars($_POST['phone'] ?? 'Not provided') . "<br>
                Birth Date: " . htmlspecialchars($_POST['birth_date']) . "<br>
                Age: " . htmlspecialchars($_POST['age']) . " (valid for " . htmlspecialchars($license_type) . " license)<br>
                Website: " . htmlspecialchars($_POST['website'] ?? 'Not provided') . "<br>
                Credit Card: " . (empty($_POST['credit_card']) ? 'Not provided' : '**** **** **** ' . substr($_POST['credit_card'], -4)) . "<br>
              </div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Custom Validation Rules</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 0 auto; padding: 20px; }
        .error { color: red; background: #ffeeee; padding: 10px; margin: 10px 0; border-left: 4px solid red; }
        .form-group { margin: 15px 0; }
        .form-row { display: flex; gap: 20px; }
        .form-row .form-group { flex: 1; }
        input, select { padding: 8px; width: 100%; border: 1px solid #ddd; box-sizing: border-box; }
        button { padding: 10px 20px; background: #007cba; color: white; border: none; cursor: pointer; }
        .form-container { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
        .help { color: #666; font-size: 0.9em; margin-top: 5px; }
        .validation-demo { background: #f0f8ff; padding: 15px; margin: 20px 0; border-left: 4px solid #007cba; }
    </style>
</head>
<body>
    <h1>Custom Validation Rules</h1>
    <p>This example demonstrates advanced custom validation patterns and business rules.</p>
    
    <?php if ($validator->hasErrors()): ?>
        <div class="error">
            <strong>⚠️ Validation Failed - <?= $validator->count() ?> error(s):</strong><br>
            <?php foreach ($validator->getErrors() as $error): ?>
                <div>• <?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div class="form-container">
        <h2>Advanced Registration Form</h2>
        <form method="post">
            <div class="form-row">
                <div class="form-group">
                    <label><strong>Username:</strong> *</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                    <div class="help">3-20 chars, letters/numbers/underscore/hyphen only</div>
                </div>
                
                <div class="form-group">
                    <label><strong>Email:</strong> *</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    <div class="help">Valid email address</div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label><strong>Password:</strong> *</label>
                    <input type="password" name="password" required>
                    <div class="help">8+ chars, uppercase, lowercase, number, special char</div>
                </div>
                
                <div class="form-group">
                    <label><strong>Confirm Password:</strong> *</label>
                    <input type="password" name="confirm_password" required>
                    <div class="help">Must match password exactly</div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label><strong>Phone:</strong> (optional)</label>
                    <input type="tel" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    <div class="help">Format: (123) 456-7890 or 123-456-7890</div>
                </div>
                
                <div class="form-group">
                    <label><strong>Birth Date:</strong> *</label>
                    <input type="date" name="birth_date" value="<?= htmlspecialchars($_POST['birth_date'] ?? '') ?>" required>
                    <div class="help">YYYY-MM-DD format</div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label><strong>Age:</strong> *</label>
                    <input type="number" name="age" value="<?= htmlspecialchars($_POST['age'] ?? '') ?>" required>
                    <div class="help">1-120, must be appropriate for license type</div>
                </div>
                
                <div class="form-group">
                    <label><strong>License Type:</strong> *</label>
                    <select name="license_type" required>
                        <option value="">Select license type</option>
                        <option value="learner" <?= ($_POST['license_type'] ?? '') === 'learner' ? 'selected' : '' ?>>Learner's Permit (15+)</option>
                        <option value="motorcycle" <?= ($_POST['license_type'] ?? '') === 'motorcycle' ? 'selected' : '' ?>>Motorcycle (16+)</option>
                        <option value="car" <?= ($_POST['license_type'] ?? '') === 'car' ? 'selected' : '' ?>>Car (18+)</option>
                        <option value="truck" <?= ($_POST['license_type'] ?? '') === 'truck' ? 'selected' : '' ?>>Truck (21+)</option>
                        <option value="commercial" <?= ($_POST['license_type'] ?? '') === 'commercial' ? 'selected' : '' ?>>Commercial (25+)</option>
                    </select>
                    <div class="help">Age requirements vary by license type</div>
                </div>
            </div>
            
            <div class="form-group">
                <label><strong>Website:</strong> (optional)</label>
                <input type="url" name="website" value="<?= htmlspecialchars($_POST['website'] ?? '') ?>">
                <div class="help">Full URL including http:// or https://</div>
            </div>
            
            <div class="form-group">
                <label><strong>Credit Card:</strong> (optional)</label>
                <input type="text" name="credit_card" value="<?= htmlspecialchars($_POST['credit_card'] ?? '') ?>">
                <div class="help">13-19 digits, spaces and dashes allowed</div>
            </div>
            
            <button type="submit">🚀 Register with Custom Validation</button>
        </form>
    </div>
    
    <div class="validation-demo">
        <h2>💡 Custom Validation Rules Explained</h2>
        
        <h3>🔒 Strong Password Validation:</h3>
        <ul>
            <li>Minimum 8 characters</li>
            <li>At least one uppercase letter (A-Z)</li>
            <li>At least one lowercase letter (a-z)</li>
            <li>At least one number (0-9)</li>
            <li>At least one special character (!@#$%^&*)</li>
        </ul>
        
        <h3>📱 Phone Number Validation:</h3>
        <ul>
            <li>Accepts: (123) 456-7890, 123-456-7890, 123.456.7890</li>
            <li>Optional country code: +1 123 456 7890</li>
            <li>10-15 digits total</li>
        </ul>
        
        <h3>💳 Credit Card Validation:</h3>
        <ul>
            <li>13-19 digits (covers Visa, MasterCard, American Express)</li>
            <li>Spaces and dashes are automatically removed</li>
            <li>Basic format check only (not Luhn algorithm)</li>
        </ul>
        
        <h3>🎯 Business Logic Validation:</h3>
        <ul>
            <li><strong>License Age Requirements:</strong> Custom rule based on license type</li>
            <li><strong>Password Confirmation:</strong> Ensures passwords match</li>
            <li><strong>Date Format:</strong> Validates proper date format</li>
            <li><strong>URL Format:</strong> Full URL validation including protocol</li>
        </ul>
    </div>
    
    <h3>🧪 Test Scenarios:</h3>
    <div style="background: #fffacd; padding: 15px; margin: 10px 0;">
        <strong>Valid Data:</strong><br>
        Username: john_doe, Email: john@example.com, Password: MyPass123!<br>
        Age: 25, License: car, Phone: (555) 123-4567<br><br>
        
        <strong>Invalid Password:</strong><br>
        Try: "password" (no uppercase, no number, no special char)<br><br>
        
        <strong>Age/License Mismatch:</strong><br>
        Try: Age 16 with Commercial license (requires 25+)<br><br>
        
        <strong>Invalid Formats:</strong><br>
        Phone: "abc-def-ghij", Credit Card: "not-a-number", Website: "just-text"
    </div>
    
    <p>
        <a href="file_upload.php">← Previous: File upload validation</a> | 
        <a href="index.php">Back to main menu</a>
    </p>
</body>
</html>
