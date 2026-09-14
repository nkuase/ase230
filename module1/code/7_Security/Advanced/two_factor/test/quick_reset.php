<?php
require_once 'config.php';

/**
 * QUICK 2FA RESET TOOL
 * Command line script to quickly reset 2FA for users
 * 
 * Usage:
 * php quick_reset.php john
 * php quick_reset.php admin
 * php quick_reset.php --all
 */

echo "=== Quick 2FA Reset Tool ===\n\n";

// Get username from command line argument
$username = isset($argv[1]) ? $argv[1] : null;

if (!$username) {
    echo "Usage: php quick_reset.php <username>\n";
    echo "       php quick_reset.php --all\n\n";
    echo "Available users: john, admin\n";
    echo "Example: php quick_reset.php john\n";
    exit(1);
}

// Reset all users
if ($username === '--all') {
    echo "Resetting 2FA for ALL users...\n\n";
    
    loadUsers();
    global $users;
    
    foreach ($users as $user => $data) {
        if ($data['totp_enabled']) {
            $resetData = [
                'totp_secret' => null,
                'totp_enabled' => false
            ];
            updateUser($user, $resetData);
            echo "✅ Reset 2FA for user: {$user}\n";
        } else {
            echo "ℹ️  2FA not enabled for: {$user}\n";
        }
    }
    
    echo "\n🎉 All users reset complete!\n";
    echo "Users can now set up 2FA again from scratch.\n";
    exit(0);
}

// Reset specific user
$user = getUser($username);
if (!$user) {
    echo "❌ Error: User '{$username}' not found\n";
    echo "Available users: john, admin\n";
    exit(1);
}

if (!$user['totp_enabled']) {
    echo "ℹ️  User '{$username}' does not have 2FA enabled\n";
    echo "Current status:\n";
    echo "  - TOTP Enabled: " . ($user['totp_enabled'] ? 'Yes' : 'No') . "\n";
    echo "  - Has Secret: " . (!empty($user['totp_secret']) ? 'Yes' : 'No') . "\n";
    exit(0);
}

echo "Resetting 2FA for user: {$username}\n";
echo "Current status:\n";
echo "  - TOTP Enabled: " . ($user['totp_enabled'] ? 'Yes' : 'No') . "\n";
echo "  - Has Secret: " . (!empty($user['totp_secret']) ? 'Yes' : 'No') . "\n\n";

// Perform reset
$resetData = [
    'totp_secret' => null,
    'totp_enabled' => false
];

$success = updateUser($username, $resetData);

if ($success) {
    echo "✅ 2FA reset successful for user: {$username}\n";
    echo "\nNext steps:\n";
    echo "1. User '{$username}' can now set up 2FA again\n";
    echo "2. Delete old entries from authenticator app\n";
    echo "3. Use setup.php or web interface to create new 2FA\n";
    echo "4. Test with: php -S localhost:8000\n";
} else {
    echo "❌ Failed to reset 2FA for user: {$username}\n";
    exit(1);
}

echo "\n=== Reset Complete ===\n";
?>
