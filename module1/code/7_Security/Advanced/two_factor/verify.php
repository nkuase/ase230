<?php
require_once 'SimpleTOTP.php';
require_once 'config.php';

/**
 * 2FA SETUP VERIFICATION ENDPOINT
 * POST /verify.php
 * Body: {"username": "john", "code": "123456"}
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['error' => 'Only POST method allowed'], 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['username']) || !isset($input['code'])) {
    sendJsonResponse(['error' => 'Username and code required'], 400);
}

$username = $input['username'];
$code = $input['code'];

// Validate code format
if (!preg_match('/^\d{6}$/', $code)) {
    sendJsonResponse(['error' => 'Code must be 6 digits'], 400);
}

// Get user
$user = getUser($username);
if (!$user) {
    sendJsonResponse(['error' => 'User not found'], 401);
}

// Check if user has a pending secret (from setup)
if (!$user['totp_secret']) {
    sendJsonResponse(['error' => 'No 2FA setup found. Please run setup first.'], 400);
}

// Check if 2FA is already enabled
if ($user['totp_enabled']) {
    sendJsonResponse(['error' => '2FA is already enabled'], 400);
}

try {
    // Decode secret
    $secret = base64_decode($user['totp_secret']);
    
    // Verify the code
    if (SimpleTOTP::verifyCode($code, $secret)) {
        // Enable 2FA for this user
        updateUser($username, ['totp_enabled' => true]);
        
        sendJsonResponse([
            'message' => '2FA setup completed successfully!',
            'status' => 'enabled',
            'next_step' => 'You can now login with 2FA'
        ]);
    } else {
        sendJsonResponse([
            'error' => 'Invalid code. Please check your authenticator app.',
            'hint' => 'Make sure your device time is synchronized'
        ], 400);
    }
    
} catch (Exception $e) {
    sendJsonResponse(['error' => 'Verification failed: ' . $e->getMessage()], 500);
}
?>
