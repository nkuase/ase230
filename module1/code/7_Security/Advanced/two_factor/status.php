<?php
require_once 'config.php';

/**
 * 2FA STATUS ENDPOINT
 * GET /status.php?username=john
 * POST /status.php with {"username": "john", "password": "Secret123"}
 * 
 * Shows current 2FA status for debugging and user information
 */

header('Content-Type: application/json');

$username = null;
$authenticated = false;

// Handle both GET and POST requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $username = isset($_GET['username']) ? $_GET['username'] : null;
    $authenticated = false; // GET requests show limited info
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input && isset($input['username'])) {
        $username = $input['username'];
        
        // Check password for detailed info
        if (isset($input['password'])) {
            $user = getUser($username);
            if ($user && $user['password'] === $input['password']) {
                $authenticated = true;
            }
        }
    }
}

if (!$username) {
    sendJsonResponse(['error' => 'Username required'], 400);
}

// Get user data
$user = getUser($username);
if (!$user) {
    sendJsonResponse(['error' => 'User not found'], 404);
}

// Basic status (available to everyone)
$basic_status = [
    'username' => $username,
    'totp_enabled' => $user['totp_enabled'],
    'has_secret' => !empty($user['totp_secret'])
];

// Detailed status (only with password authentication)
if ($authenticated) {
    $detailed_status = [
        'username' => $username,
        'user_id' => $user['id'],
        'totp_enabled' => $user['totp_enabled'],
        'has_secret' => !empty($user['totp_secret']),
        'secret_length' => !empty($user['totp_secret']) ? strlen($user['totp_secret']) : 0,
        'setup_status' => [
            'ready_for_setup' => !$user['totp_enabled'] && empty($user['totp_secret']),
            'setup_in_progress' => !$user['totp_enabled'] && !empty($user['totp_secret']),
            'fully_enabled' => $user['totp_enabled'] && !empty($user['totp_secret']),
            'inconsistent_state' => $user['totp_enabled'] && empty($user['totp_secret'])
        ],
        'available_actions' => []
    ];
    
    // Determine available actions
    if (!$user['totp_enabled']) {
        $detailed_status['available_actions'][] = 'setup_2fa';
    } else {
        $detailed_status['available_actions'][] = 'reset_2fa';
        $detailed_status['available_actions'][] = 'login_with_2fa';
    }
    
    // Add troubleshooting info
    if ($user['totp_enabled'] && empty($user['totp_secret'])) {
        $detailed_status['warning'] = 'Inconsistent state: 2FA enabled but no secret stored';
        $detailed_status['suggested_action'] = 'Reset 2FA and set up again';
    }
    
    sendJsonResponse($detailed_status);
} else {
    sendJsonResponse($basic_status);
}
?>
