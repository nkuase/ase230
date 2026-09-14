<?php
require_once 'bearer_auth.php';

// Get the token from request
$token = getBearerToken();

if (!$token) {
    http_response_code(401);
    echo json_encode(['error' => 'Token required']);
    exit;
}

// Validate token
$user = isValidToken($token);
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid token']);
    exit;
}

// Success! Return protected data
echo json_encode([
    'message' => 'Welcome to protected API!',
    'user' => $user,
    'data' => ['item1', 'item2', 'item3']
]);
?>