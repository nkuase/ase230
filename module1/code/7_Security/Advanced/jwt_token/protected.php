<?php
require_once 'SimpleJWT.php';
require_once 'config.php';

/**
 * PROTECTED ROUTE - Requires Valid JWT Token
 * GET /protected.php
 * Header: Authorization: Bearer [token]
 */

// Get authorization header
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (!$authHeader) {
    sendJsonResponse(['error' => 'Authorization header missing'], 401);
}

// Extract token from "Bearer [token]" format
if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
    sendJsonResponse(['error' => 'Invalid authorization format'], 401);
}

$token = $matches[1];

try {
    // Verify and decode token
    $payload = SimpleJWT::decode($token, JWT_SECRET);
    
    // Token is valid - return protected data
    sendJsonResponse([
        'message' => 'Access granted to protected resource',
        'user_data' => [
            'user_id' => $payload['user_id'],
            'username' => $payload['username'],
            'role' => $payload['role']
        ],
        'token_info' => [
            'issued_at' => date('Y-m-d H:i:s', $payload['iat']),
            'expires_at' => date('Y-m-d H:i:s', $payload['exp']),
            'time_remaining' => $payload['exp'] - time() . ' seconds'
        ],
        'protected_data' => [
            'secret_message' => 'This is confidential information!',
            'server_time' => date('Y-m-d H:i:s'),
            'access_level' => $payload['role']
        ]
    ]);
    
} catch (Exception $e) {
    sendJsonResponse([
        'error' => 'Token verification failed',
        'reason' => $e->getMessage()
    ], 401);
}
?>
