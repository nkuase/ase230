<?php
/**
 * Bearer Token Authentication Helper
 * Simple functions for handling bearer token authentication
 */

/**
 * Extract bearer token from Authorization header
 * @return string|null The token or null if not found
 */
function getBearerToken() {
    $headers = getallheaders();
    
    // Check if Authorization header exists
    if (isset($headers['Authorization'])) {
        // Extract token from "Bearer TOKEN_HERE" format
        if (preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches)) {
            return trim($matches[1]);
        }
    }
    
    return null;
}

/**
 * Simple token validation (for demo purposes)
 * In real applications, check dataabase with expiration
 * @param string $token
 * @return string|false Username if valid, false if invalid
 */
function isValidToken($token) {
    // Demo tokens - in real app, check database
    $validTokens = [
        'john-demo-token' => 'john',
        'admin-demo-token' => 'admin'
    ];
    
    return isset($validTokens[$token]) ? $validTokens[$token] : false;
}

/**
 * Generate a secure random token
 * @return string
 */
function generateSecureToken() {
    return bin2hex(random_bytes(32)); // 64 character hex string
}

/**
 * Send JSON error response and exit
 * @param int $statusCode HTTP status code
 * @param string $message Error message
 */
function sendJsonError($statusCode, $message) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode(['error' => $message]);
    exit;
}

/**
 * Send JSON success response
 * @param array $data Data to send
 */
function sendJsonSuccess($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
}

/**
 * Require authentication for an endpoint
 * Call this at the start of protected endpoints
 * @return string Username of authenticated user
 */
function requireAuth() {
    $token = getBearerToken();
    
    if (!$token) {
        sendJsonError(401, 'Bearer token required');
    }
    
    $user = isValidToken($token);
    if (!$user) {
        sendJsonError(401, 'Invalid or expired token');
    }
    
    return $user;
}
?>