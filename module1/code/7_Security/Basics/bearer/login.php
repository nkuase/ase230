<?php
/**
 * Simple Login Endpoint
 * Demonstrates how to authenticate users and return bearer tokens
 */

require_once 'bearer_auth.php';

// Set JSON content type
header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonError(405, 'Method not allowed. Use POST.');
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Check if username and password are provided
if (!isset($input['username']) || !isset($input['password'])) {
    sendJsonError(400, 'Username and password required');
}

$username = $input['username'];
$password = $input['password'];

// Simple user database (in real app, use database with hashed passwords)
$users = [
    'john' => 'Secret123',
    'admin' => 'Admin123'
];

// Validate credentials
if (!isset($users[$username]) || $users[$username] !== $password) {
    sendJsonError(401, 'Invalid username or password');
}

// Generate token for valid user
$token = generateSecureToken();

// In a real application, you would:
// 1. Store the token in database with expiration
// 2. Hash the password before comparing
// 3. Use proper password verification functions

// For demo purposes, we'll use predefined tokens
$demoTokens = [
    'john' => 'john-demo-token',
    'admin' => 'admin-demo-token'
];

$token = $demoTokens[$username];

// Return success with token
sendJsonSuccess([
    'message' => 'Login successful',
    'token' => $token,
    'user' => $username,
    'expires_in' => 3600 // 1 hour (demo value)
]);
?>