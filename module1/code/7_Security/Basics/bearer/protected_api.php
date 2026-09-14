<?php
/**
 * Protected API Endpoint
 * Demonstrates bearer token authentication for API access
 */

require_once 'bearer_auth.php';

// Require authentication - this will exit if no valid token
$user = requireAuth();

// If we reach here, user is authenticated!
// $user contains the username

// Example protected data
$protectedData = [
    'message' => 'Welcome to the protected API!',
    'authenticated_user' => $user,
    'timestamp' => date('Y-m-d H:i:s'),
    'data' => [
        'secret_info' => 'This is confidential data',
        'user_permissions' => ['read', 'write'],
        'server_info' => 'PHP ' . phpversion()
    ]
];

// Add user-specific data
if ($user === 'admin') {
    $protectedData['admin_data'] = [
        'total_users' => 2,
        'server_status' => 'healthy',
        'admin_tools' => ['user_management', 'system_logs']
    ];
}

if ($user === 'john') {
    $protectedData['user_data'] = [
        'enrolled_courses' => ['ASE230'],
        'grades' => ['A', 'B+', 'A-'],
        'next_assignment' => 'Bearer Token Project'
    ];
}

// Return the protected data
sendJsonSuccess($protectedData);
?>