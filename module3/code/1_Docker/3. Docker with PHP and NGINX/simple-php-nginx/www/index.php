<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP + NGINX with Docker</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f4f4f4;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success { color: green; font-weight: bold; }
        .info { background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🐳 PHP + NGINX Docker Setup</h1>
        
        <div class="success">
            ✅ Your containerized web application is working!
        </div>
        
        <div class="info">
            <h3>Server Information:</h3>
            <ul>
                <li><strong>PHP Version:</strong> <?= phpversion() ?></li>
                <li><strong>Server:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'NGINX' ?></li>
                <li><strong>Current Time:</strong> <?= date('Y-m-d H:i:s') ?></li>
                <li><strong>Container Host:</strong> <?= gethostname() ?></li>
            </ul>
        </div>
        
        <h3>Test Links:</h3>
        <p>
            <a href="/info.php">📊 PHP Info</a> |
            <a href="/test.php">🧪 Test Script</a>
        </p>
        
        <div class="info">
            <h3>🏗️ Architecture:</h3>
            <p>NGINX Container → PHP Container → Your Application</p>
        </div>
    </div>
</body>
</html>