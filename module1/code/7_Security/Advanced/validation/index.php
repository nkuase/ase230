<h1>Input Validation Demo - Super Simple</h1>

<h2>What is input validation?</h2>
<p>Input validation checks if user data is safe and correct before using it.</p>

<h2>Why validation matters:</h2>
<ul>
    <li><strong>Security:</strong> Prevents SQL injection and XSS attacks</li>
    <li><strong>Data Quality:</strong> Ensures clean, consistent data</li>
    <li><strong>User Experience:</strong> Clear error messages help users</li>
</ul>

<h2>Examples:</h2>
<p><a href="simple_validation.php">1. Simple Validation (without class)</a></p>
<p><a href="basic_example.php">2. Basic Validator Example</a></p>
<p><a href="registration_form.php">3. Complete Registration Form</a></p>
<p><a href="api_validation.php">4. API Validation Example</a></p>
<p><a href="file_upload.php">5. File Upload Validation</a></p>
<p><a href="custom_validation.php">6. Custom Validation Rules</a></p>

<h2>Code Files:</h2>
<p><a href="Validator.php">View Validator.php Class</a></p>

<h3>⚠️ Remember: NEVER trust user input!</h3>
<div style="background: #ffeeee; padding: 10px; border-left: 4px solid red;">
    <strong>Dangerous:</strong> $sql = "SELECT * FROM users WHERE email = '$_POST[email]'";
    <br><strong>Safe:</strong> Validate first, then use prepared statements!
</div>
