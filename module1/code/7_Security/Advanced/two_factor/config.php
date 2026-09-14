<?php
// Demo users database (in real app, use actual database)
$users = [
    'john' => [
        'id' => 1,
        'username' => 'john',
        'password' => 'Secret123',
        'totp_secret' => null,  // Will be set when 2FA is enabled
        'totp_enabled' => false
    ],
    'admin' => [
        'id' => 2,
        'username' => 'admin', 
        'password' => 'Admin123',
        'totp_secret' => null,
        'totp_enabled' => false
    ]
];

// Session file to persist user data (simple file-based storage)
$users_file = __DIR__ . '/data/users_data.json';
// error_log(print_r($users_file, true), 3, __DIR__ . '/data/debug.log');

// Load users from file if exists
function loadUsers() {
    global $users, $users_file;
    if (file_exists($users_file)) {
        $data = json_decode(file_get_contents($users_file), true);
        if ($data) {
            $users = $data;
        }
    }
}

// Save users to file
function saveUsers() {
    global $users, $users_file;
    $dir = dirname($users_file);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));
}

// Helper function to send JSON response
function sendJsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Helper function to get user by username
function getUser($username) {
    global $users;
    loadUsers();
    return isset($users[$username]) ? $users[$username] : null;
}

// Helper function to update user
function updateUser($username, $data) {
    global $users;
    loadUsers();
    if (isset($users[$username])) {
        $users[$username] = array_merge($users[$username], $data);
        saveUsers();
        return true;
    }
    return false;
}

// Initialize users file
loadUsers();
?>
