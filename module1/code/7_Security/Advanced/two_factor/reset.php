<?php
require_once 'SimpleTOTP.php';
require_once 'config.php';

/**
 * 2FA RESET ENDPOINT
 * POST /reset.php
 * Body: {"username": "john", "password": "Secret123", "confirm_reset": true}
 * 
 * SECURITY LEVELS:
 * - Level 1: Password verification only (current implementation)
 * - Level 2: Could require current TOTP code
 * - Level 3: Could require admin approval
 * - Level 4: Could require email verification
 */

header('Content-Type: application/json');

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
$confirm_reset = isset($input['confirm_reset']) ? $input['confirm_reset'] : false;

// Verify user credentials
$user = getUser($username);
if (!$user) {
    sendJsonResponse(['error' => 'User not found'], 401);
}

// Check password (in real app, use password_verify())
if ($user['password'] !== $password) {
    sendJsonResponse(['error' => 'Invalid credentials'], 401);
}

// Check if user has 2FA enabled
if (!$user['totp_enabled']) {
    sendJsonResponse(['error' => '2FA is not enabled for this user'], 400);
}

// Security check: require explicit confirmation
if (!$confirm_reset) {
    sendJsonResponse([
        'error' => 'Reset confirmation required',
        'message' => 'To reset 2FA, send confirm_reset: true in your request',
        'warning' => 'This will disable 2FA and require re-setup',
        'current_status' => [
            'totp_enabled' => $user['totp_enabled'],
            'has_secret' => !empty($user['totp_secret'])
        ]
    ], 400);
}

try {
    // Reset 2FA for user
    $resetData = [
        'totp_secret' => null,      // Clear the secret
        'totp_enabled' => false     // Disable 2FA
    ];
    
    $success = updateUser($username, $resetData);
    
    if ($success) {
        sendJsonResponse([
            'message' => '2FA has been successfully reset',
            'username' => $username,
            'status' => 'reset_complete',
            'next_steps' => [
                '1. 2FA is now disabled for this user',
                '2. User can now set up 2FA again using /setup.php',
                '3. Old authenticator entries should be deleted',
                '4. New QR code will be generated during setup'
            ],
            'security_note' => 'User account is now secured with password only'
        ]);
    } else {
        sendJsonResponse(['error' => 'Failed to reset 2FA'], 500);
    }
    
} catch (Exception $e) {
    sendJsonResponse(['error' => 'Reset failed: ' . $e->getMessage()], 500);
}
?>
