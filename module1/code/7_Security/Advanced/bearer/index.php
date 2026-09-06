<?php
// Simple landing page for the bearer token demo
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bearer Token Authentication Demo</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1 {
            color: #4a5568;
            text-align: center;
            margin-bottom: 10px;
            font-size: 2.5em;
        }
        .subtitle {
            text-align: center;
            color: #718096;
            margin-bottom: 30px;
            font-size: 1.2em;
        }
        .demo-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .demo-card {
            background: #f7fafc;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #4299e1;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .demo-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .demo-card h3 {
            margin-top: 0;
            color: #2d3748;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .demo-card p {
            color: #4a5568;
            margin-bottom: 15px;
        }
        .demo-card a {
            display: inline-block;
            background: #4299e1;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            transition: background 0.2s;
        }
        .demo-card a:hover {
            background: #3182ce;
        }
        .info-section {
            margin: 30px 0;
            padding: 20px;
            background: #edf2f7;
            border-radius: 8px;
        }
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .users-table th,
        .users-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        .users-table th {
            background: #f7fafc;
            font-weight: 600;
            color: #2d3748;
        }
        .users-table tr:hover {
            background: #f7fafc;
        }
        .code-block {
            background: #2d3748;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            overflow-x: auto;
            margin: 15px 0;
        }
        .endpoint {
            background: #48bb78;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 0.9em;
        }
        .method {
            background: #ed8936;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 0.8em;
            margin-right: 8px;
        }
        .security-warning {
            background: #fed7d7;
            border: 1px solid #fc8181;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
        }
        .security-warning h4 {
            color: #c53030;
            margin-top: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Bearer Token Authentication Demo</h1>
        <p class="subtitle">Learn API authentication with hands-on PHP examples</p>
        
        <div class="demo-links">
            <div class="demo-card">
                <h3>🌐 Interactive Demo</h3>
                <p>Try the full authentication flow in your browser with our interactive client.</p>
                <a href="client_demo.html">Launch Demo →</a>
            </div>
            
            <div class="demo-card">
                <h3>🔧 API Endpoints</h3>
                <p>Test the raw API endpoints directly:</p>
                <div style="margin: 10px 0;">
                    <span class="method">POST</span><span class="endpoint">login.php</span>
                </div>
                <div style="margin: 10px 0;">
                    <span class="method">GET</span><span class="endpoint">protected_api.php</span>
                </div>
            </div>
            
            <div class="demo-card">
                <h3>💻 Command Line</h3>
                <p>Test with cURL commands and see the raw HTTP requests and responses.</p>
                <a href="test_curl.sh" download>Download Script →</a>
            </div>
            
            <div class="demo-card">
                <h3>📚 Documentation</h3>
                <p>Read the complete guide with code explanations and security notes.</p>
                <a href="doc/README.md">View README →</a>
            </div>
        </div>
        
        <div class="info-section">
            <h3>📋 Test Accounts</h3>
            <p>Use these accounts to test the authentication system:</p>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Password</th>
                        <th>Token</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>john</td>
                        <td>Secret123</td>
                        <td>john-demo-token</td>
                        <td>User</td>
                    </tr>
                    <tr>
                        <td>admin</td>
                        <td>Admin123</td>
                        <td>admin-demo-token</td>
                        <td>Administrator</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="info-section">
            <h3>🚀 Quick Test</h3>
            <p>Try these curl commands to test the API:</p>
            
            <h4>1. Login to get a token:</h4>
            <div class="code-block">curl -X POST http://localhost:8000/login.php \
     -H "Content-Type: application/json" \
     -d '{"username":"john","password":"Secret123"}'</div>
            
            <h4>2. Use the token to access protected data:</h4>
            <div class="code-block">curl -H "Authorization: Bearer john-demo-token" \
     http://localhost:8000/protected_api.php</div>
        </div>
        
        <div class="security-warning">
            <h4>⚠️ Educational Purpose Only</h4>
            <p>This demo is designed for learning. In production applications:</p>
            <ul>
                <li>Always use HTTPS to protect tokens in transit</li>
                <li>Store hashed passwords, never plain text</li>
                <li>Implement token expiration and refresh mechanisms</li>
                <li>Use secure, randomly generated tokens</li>
                <li>Store tokens securely in a database</li>
                <li>Implement proper input validation and sanitization</li>
            </ul>
        </div>
        
        <div class="info-section">
            <h3>🎯 Learning Goals</h3>
            <p>After completing this demo, you should understand:</p>
            <ul>
                <li>How bearer token authentication works</li>
                <li>How to extract tokens from HTTP headers in PHP</li>
                <li>How to validate tokens and authenticate users</li>
                <li>How to send bearer tokens from client applications</li>
                <li>Proper HTTP status codes for authentication scenarios</li>
                <li>Security considerations for token-based authentication</li>
            </ul>
        </div>
    </div>
    
    <div style="text-align: center; color: white; margin-top: 20px; opacity: 0.8;">
        <p>Bearer Token Authentication Demo - ASE230 Course Material</p>
    </div>
</body>
</html>