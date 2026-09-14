<?php
// Configuration settings
define('JWT_SECRET', 'your-super-secret-key-change-this-in-production');

// Demo users (in real app, this would be in a database)
$users = [
    'john' => [
        'id' => 1,
        'username' => 'john',
        'password' => 'Secret123', // In real app, use password_hash()
        'role' => 'user'
    ],
    'admin' => [
        'id' => 2,
        'username' => 'admin',
        'password' => 'Admin123',
        'role' => 'admin'
    ]
];

// Helper function to send JSON response
function sendJsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>
