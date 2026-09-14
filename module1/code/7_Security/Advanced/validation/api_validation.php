<?php
require_once 'Validator.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Set content type to JSON for API responses
    header('Content-Type: application/json');
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // If no JSON, check for form data (for testing with HTML form)
    if (!$input && $_POST) {
        $input = $_POST;
    }
    
    $validator = new Validator();
    
    // Validate API input
    $validator->required($input['name'] ?? '', 'Name')
              ->minLength($input['name'] ?? '', 2, 'Name')
              ->maxLength($input['name'] ?? '', 100, 'Name');
    
    $validator->required($input['email'] ?? '', 'Email')
              ->email($input['email'] ?? '', 'Email');
    
    $validator->required($input['age'] ?? '', 'Age')
              ->numeric($input['age'] ?? '', 'Age')
              ->min($input['age'] ?? 0, 1, 'Age')
              ->max($input['age'] ?? 0, 150, 'Age');
    
    // Return validation errors
    if ($validator->hasErrors()) {
        http_response_code(400); // Bad Request
        echo json_encode([
            'success' => false,
            'errors' => $validator->getErrors(),
            'message' => 'Validation failed',
            'error_count' => $validator->count()
        ]);
        exit;
    }
    
    // Process valid data
    echo json_encode([
        'success' => true,
        'message' => 'User created successfully',
        'data' => [
            'name' => $input['name'],
            'email' => $input['email'],
            'age' => (int)$input['age']
        ]
    ]);
    exit;
}

// For GET requests, show HTML documentation (no JSON header)
?>
<!DOCTYPE html>
<html>
<head>
    <title>API Validation Example</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
        .code { background: #f4f4f4; padding: 10px; border-left: 4px solid #007cba; overflow-x: auto; }
        .test-form { background: #f9f9f9; padding: 15px; border: 1px solid #ddd; margin: 20px 0; }
        .response { background: #000; color: #0f0; padding: 10px; font-family: monospace; margin: 10px 0; }
        button { padding: 8px 15px; background: #007cba; color: white; border: none; cursor: pointer; }
        input { padding: 5px; margin: 5px; width: 200px; }
    </style>
</head>
<body>
    <h1>API Validation Example</h1>
    <p>This endpoint validates JSON data sent via POST and returns JSON responses.</p>
    
    <h2>📡 API Documentation</h2>
    <div class="code">
        <strong>Endpoint:</strong> POST /api_validation.php<br>
        <strong>Content-Type:</strong> application/json<br><br>
        
        <strong>Request Body:</strong><br>
        {<br>
        &nbsp;&nbsp;"name": "John Doe",<br>
        &nbsp;&nbsp;"email": "john@example.com",<br>
        &nbsp;&nbsp;"age": 25<br>
        }<br><br>
        
        <strong>Success Response (200):</strong><br>
        {<br>
        &nbsp;&nbsp;"success": true,<br>
        &nbsp;&nbsp;"message": "User created successfully",<br>
        &nbsp;&nbsp;"data": { "name": "John Doe", "email": "john@example.com", "age": 25 }<br>
        }<br><br>
        
        <strong>Error Response (400):</strong><br>
        {<br>
        &nbsp;&nbsp;"success": false,<br>
        &nbsp;&nbsp;"message": "Validation failed",<br>
        &nbsp;&nbsp;"errors": ["Name is required", "Email must be a valid email"],<br>
        &nbsp;&nbsp;"error_count": 2<br>
        }
    </div>
    
    <h2>🧪 Test the API</h2>
    <div class="test-form">
        <h3>Quick Test Form (converts to JSON automatically)</h3>
        <form id="testForm">
            Name: <input type="text" id="name" placeholder="John Doe"><br>
            Email: <input type="email" id="email" placeholder="john@example.com"><br>
            Age: <input type="number" id="age" placeholder="25"><br>
            <button type="submit">Send API Request</button>
            <button type="button" onclick="sendBadData()">Send Invalid Data</button>
            <button type="button" onclick="sendEmptyData()">Send Empty Data</button>
        </form>
        
        <h4>Response:</h4>
        <div id="response" class="response">Click a button above to test the API...</div>
    </div>
    
    <h2>💡 What This Example Shows</h2>
    <div style="background: #f0f8ff; padding: 15px;">
        <h3>API Validation Benefits:</h3>
        <ul>
            <li><strong>JSON Input/Output:</strong> Perfect for REST APIs</li>
            <li><strong>HTTP Status Codes:</strong> 400 for validation errors, 200 for success</li>
            <li><strong>Structured Errors:</strong> Machine-readable error format</li>
            <li><strong>Same Validator:</strong> Reuse validation logic across web forms and APIs</li>
        </ul>
        
        <h3>Real-World Usage:</h3>
        <ul>
            <li><strong>Mobile Apps:</strong> Send JSON data to your PHP API</li>
            <li><strong>AJAX Requests:</strong> Validate form data without page refresh</li>
            <li><strong>Microservices:</strong> Service-to-service communication</li>
            <li><strong>Third-party Integration:</strong> Validate webhook payloads</li>
        </ul>
    </div>
    
    <h3>🔨 cURL Example:</h3>
    <div class="code">
curl -X POST http://localhost:8000/api_validation.php \<br>
&nbsp;&nbsp;&nbsp;&nbsp;-H "Content-Type: application/json" \<br>
&nbsp;&nbsp;&nbsp;&nbsp;-d '{"name":"John","email":"john@example.com","age":25}'
    </div>
    
    <script>
        document.getElementById('testForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const data = {
                name: document.getElementById('name').value,
                email: document.getElementById('email').value,
                age: document.getElementById('age').value
            };
            
            sendApiRequest(data);
        });
        
        function sendBadData() {
            sendApiRequest({
                name: "x",  // Too short
                email: "bad-email",  // Invalid format
                age: -5  // Too young
            });
        }
        
        function sendEmptyData() {
            sendApiRequest({});
        }
        
        function sendApiRequest(data) {
            fetch('api_validation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('response').innerHTML = 
                    'Status: ' + (data.success ? '✅ SUCCESS' : '❌ ERROR') + '\n\n' +
                    JSON.stringify(data, null, 2);
            })
            .catch(error => {
                document.getElementById('response').innerHTML = 'Network Error: ' + error;
            });
        }
    </script>
    
    <p>
        <a href="registration_form.php">← Previous: Registration form</a> | 
        <a href="file_upload.php">Next: File upload validation →</a>
    </p>
</body>
</html>
