<?php
require_once __DIR__ . '/../SimpleTOTP.php';

/**
 * 2FA TOTP Testing Script
 * Run this to understand how TOTP works
 */

echo "=== Two-Factor Authentication (TOTP) Demo ===\n\n";

// Step 1: Generate a secret
echo "1. Generating Secret Key...\n";
$secret = SimpleTOTP::generateSecret(16);
$secret_base32 = SimpleTOTP::base32Encode($secret);

echo "Secret (binary): " . bin2hex($secret) . "\n";
echo "Secret (Base32): " . $secret_base32 . "\n\n";

// Step 2: Generate current TOTP code
echo "2. Generating TOTP Code...\n";
$timestamp = time();
$code = SimpleTOTP::generateCode($secret, $timestamp);

echo "Current timestamp: " . $timestamp . "\n";
echo "Time window: " . floor($timestamp / 30) . " (changes every 30 seconds)\n";
echo "Generated code: " . $code . "\n\n";

// Step 3: Verify the code
echo "3. Verifying TOTP Code...\n";
$is_valid = SimpleTOTP::verifyCode($code, $secret, $timestamp);
echo "Code verification: " . ($is_valid ? "✅ VALID" : "❌ INVALID") . "\n\n";

// Step 4: Show how codes change over time
echo "4. Time-based Code Generation...\n";
for ($i = -2; $i <= 2; $i++) {
    $test_time = $timestamp + ($i * 30);  // ±60 seconds
    $test_code = SimpleTOTP::generateCode($secret, $test_time);
    $time_label = $i === 0 ? "CURRENT" : ($i > 0 ? "+{$i}×30s" : "{$i}×30s");
    
    echo sprintf("Time %s: %s → Code: %s\n", 
        $time_label, 
        date('H:i:s', $test_time), 
        $test_code
    );
}
echo "\n";

// Step 5: QR Code URL
echo "5. QR Code for Authenticator App...\n";
$qr_url = SimpleTOTP::getQRCodeURL($secret, 'testuser', 'Demo App');
echo "QR Code URL: " . $qr_url . "\n\n";

// Step 6: Test invalid codes
echo "6. Testing Invalid Codes...\n";
$invalid_codes = ['000000', '123456', '999999'];

foreach ($invalid_codes as $invalid_code) {
    $is_valid = SimpleTOTP::verifyCode($invalid_code, $secret, $timestamp);
    echo "Code {$invalid_code}: " . ($is_valid ? "✅ VALID" : "❌ INVALID") . "\n";
}
echo "\n";

// Step 7: Clock drift tolerance
echo "7. Clock Drift Tolerance Test...\n";
$old_code = SimpleTOTP::generateCode($secret, $timestamp - 30);  // Previous window
$future_code = SimpleTOTP::generateCode($secret, $timestamp + 30);  // Next window

echo "Previous window code: {$old_code} → " . 
     (SimpleTOTP::verifyCode($old_code, $secret, $timestamp) ? "✅ ACCEPTED" : "❌ REJECTED") . "\n";
echo "Current window code:  {$code} → " . 
     (SimpleTOTP::verifyCode($code, $secret, $timestamp) ? "✅ ACCEPTED" : "❌ REJECTED") . "\n";
echo "Future window code:   {$future_code} → " . 
     (SimpleTOTP::verifyCode($future_code, $secret, $timestamp) ? "✅ ACCEPTED" : "❌ REJECTED") . "\n";

echo "\n=== Demo Complete! ===\n";
echo "TOTP allows ±1 time window (±30 seconds) for clock synchronization.\n";
echo "Codes change every 30 seconds and are cryptographically secure.\n";
?>
