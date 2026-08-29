<?php
require_once 'SimpleTOTP.php';
require_once 'config.php';

/**
 * 2FA SETUP ENDPOINT - UPDATED VERSION
 * POST /setup.php
 * Body: {"username": "john", "password": "Secret123", "force_reset": false}
 * 
 * NEW FEATURES:
 * - Allows re-setup with force_reset parameter
 * - Better error messages for existing 2FA
 * - Option to reset and setup in one operation
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
$force_reset = isset($input['force_reset']) ? $input['force_reset'] : false;

// Verify user credentials
$user = getUser($username);
if (!$user) {
    sendJsonResponse(['error' => 'User not found'], 401);
}

// Check password (in real app, use password_verify())
if ($user['password'] !== $password) {
    sendJsonResponse(['error' => 'Invalid credentials'], 401);
}

// Handle existing 2FA setup
if ($user['totp_enabled']) {
    if (!$force_reset) {
        sendJsonResponse([
            'error' => '2FA is already enabled for this user',
            'current_status' => [
                'totp_enabled' => true,
                'setup_date' => 'Previously configured'
            ],
            'options' => [
                'reset_first' => 'Use POST /reset.php to reset 2FA first',
                'force_reset' => 'Add "force_reset": true to this request to reset and re-setup',
                'check_status' => 'Use POST /status.php to check current status'
            ],
            'security_warning' => 'Resetting 2FA will require new authenticator setup'
        ], 400);
    }
    
    // Force reset requested - reset first, then continue with setup
    $resetData = [
        'totp_secret' => null,
        'totp_enabled' => false
    ];
    updateUser($username, $resetData);
    
    // Reload user data after reset
    $user = getUser($username);
}

try {
    // Generate new TOTP secret
    $secret = SimpleTOTP::generateSecret();
    
    // Store secret temporarily (not yet enabled)
    updateUser($username, ['totp_secret' => base64_encode($secret)]);
    
    // Generate QR code URLs from multiple services for fallback
    $qr_urls = SimpleTOTP::getQRCodeURLs($secret, $username, 'PHP 2FA Demo');
    
    // Primary QR code URL (using QR Server API)
    $primary_qr_url = SimpleTOTP::getQRCodeURL($secret, $username, 'PHP 2FA Demo');
    
    // Generate current code for verification (demo purposes only)
    $current_code = SimpleTOTP::generateCode($secret);
    
    $response = [
        'message' => $force_reset ? 'Previous 2FA reset, new setup ready' : 'Scan QR code with your authenticator app',
        'qr_code_url' => $primary_qr_url,
        'qr_code_urls' => $qr_urls,
        'manual_entry_key' => SimpleTOTP::base32Encode($secret),
        'current_code' => $current_code,  // For demo purposes only
        'service_info' => [
            'primary' => 'QR Server API (api.qrserver.com)',
            'backup' => 'QuickChart (quickchart.io)',
            'note' => 'Google Charts API was deprecated and replaced'
        ],
        'instructions' => [
            '1. DELETE old entries from your authenticator app if this is a reset',
            '2. Scan the QR code with Google Authenticator or similar app',
            '3. Or manually enter the key if QR scan fails',
            '4. If QR code doesn\'t load, try the backup URL in qr_code_urls.quickchart',
            '5. Enter the 6-digit code from your app to complete setup'
        ]
    ];
    
    if ($force_reset) {
        $response['reset_performed'] = true;
        $response['warning'] = 'Previous 2FA configuration was reset. Set up your authenticator app with the new QR code.';
    }
    
    sendJsonResponse($response);
    
} catch (Exception $e) {
    sendJsonResponse(['error' => 'Setup failed: ' . $e->getMessage()], 500);
}
?>
