<?php
require_once 'SimpleJWT.php';
require_once 'config.php';

/**
 * LOGIN ENDPOINT - Generates JWT Token
 * POST /login.php
 * Body: {"username": "john", "password": "Secret123"}
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['error' => 'Only POST method allowed'], 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['username']) || !isset($input['password'])) {
    sendJsonResponse(['error' => 'Username and password required'], 400);
}

$username = $input['username'];
$password = $input['password'];

// Check if user exists
if (!isset($users[$username])) {
    sendJsonResponse(['error' => 'User not found'], 401);
}

$user = $users[$username];

// Verify password (in real app, use password_verify())
if ($user['password'] !== $password) {
    sendJsonResponse(['error' => 'Invalid credentials'], 401);
}

// Create JWT payload
$payload = [
    'user_id' => $user['id'],
    'username' => $user['username'],
    'role' => $user['role'],
    'iat' => time(),              // Issued at
    'exp' => time() + 3600        // Expires in 1 hour
];

try {
    // Generate token
    $token = SimpleJWT::encode($payload, JWT_SECRET);
    
    sendJsonResponse([
        'message' => 'Login successful',
        'token' => $token,
        'expires_in' => 3600,
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role']
        ]
    ]);
    
} catch (Exception $e) {
    sendJsonResponse(['error' => 'Token generation failed'], 500);
}
?>
