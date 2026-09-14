<?php
require_once 'SimpleTOTP.php';
require_once 'config.php';

/**
 * 2FA LOGIN ENDPOINT
 * POST /login.php
 * Body: {"username": "john", "password": "Secret123", "totp_code": "123456"}
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
$totp_code = $input['totp_code'] ?? null;

// Get user
$user = getUser($username);
if (!$user) {
    sendJsonResponse(['error' => 'User not found'], 401);
}

// Verify password (in real app, use password_verify())
if ($user['password'] !== $password) {
    sendJsonResponse(['error' => 'Invalid credentials'], 401);
}

// Check if 2FA is enabled for this user
if (!$user['totp_enabled']) {
    // Login without 2FA (user hasn't set it up yet)
    sendJsonResponse([
        'message' => 'Login successful (2FA not enabled)',
        'user' => [
            'username' => $user['username'],
            'id' => $user['id']
        ],
        'totp_enabled' => false,
        'suggestion' => 'Consider enabling 2FA for better security'
    ]);
}

// 2FA is enabled - require TOTP code
if (!$totp_code) {
    sendJsonResponse([
        'error' => '2FA code required',
        'message' => 'Please provide the 6-digit code from your authenticator app'
    ], 400);
}

// Validate TOTP code format
if (!preg_match('/^\d{6}$/', $totp_code)) {
    sendJsonResponse(['error' => 'TOTP code must be 6 digits'], 400);
}

try {
    // Decode secret
    $secret = base64_decode($user['totp_secret']);
    
    // Verify TOTP code
    if (SimpleTOTP::verifyCode($totp_code, $secret)) {
        // Successful 2FA login
        sendJsonResponse([
            'message' => 'Login successful with 2FA!',
            'user' => [
                'username' => $user['username'],
                'id' => $user['id']
            ],
            'totp_enabled' => true,
            'login_time' => date('Y-m-d H:i:s'),
            'security_level' => 'high'
        ]);
    } else {
        sendJsonResponse([
            'error' => 'Invalid 2FA code',
            'hint' => 'Check your authenticator app and ensure device time is correct'
        ], 401);
    }
    
} catch (Exception $e) {
    sendJsonResponse(['error' => 'Login failed: ' . $e->getMessage()], 500);
}
?>
