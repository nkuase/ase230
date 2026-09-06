<?php
require_once 'SimpleJWT.php';
require_once 'config.php';

/**
 * JWT Token Testing Script
 * Run this to see JWT encoding/decoding in action
 */

echo "=== JWT Token Demo ===\n\n";

// Step 1: Create a token
echo "1. Creating JWT Token...\n";
$payload = [
    'user_id' => 123,
    'username' => 'john',
    'role' => 'user',
    'iat' => time(),
    'exp' => time() + 3600  // 1 hour
];

echo "Payload: " . json_encode($payload, JSON_PRETTY_PRINT) . "\n";

try {
    $token = SimpleJWT::encode($payload, JWT_SECRET);
    echo "✅ Token created successfully!\n";
    echo "Token: " . $token . "\n\n";
    
    // Step 2: Decode the token
    echo "2. Decoding JWT Token...\n";
    $decoded = SimpleJWT::decode($token, JWT_SECRET);
    echo "✅ Token decoded successfully!\n";
    echo "Decoded payload: " . json_encode($decoded, JSON_PRETTY_PRINT) . "\n\n";
    
    // Step 3: Show token structure
    echo "3. Token Structure Analysis...\n";
    $parts = explode('.', $token);
    echo "Token has " . count($parts) . " parts:\n";
    
    echo "Header: " . $parts[0] . "\n";
    echo "Payload: " . $parts[1] . "\n";
    echo "Signature: " . $parts[2] . "\n\n";
    
    // Decode header and payload to show contents
    $header = json_decode(base64_decode(str_pad(strtr($parts[0], '-_', '+/'), strlen($parts[0]) % 4, '=', STR_PAD_RIGHT)), true);
    echo "Header decoded: " . json_encode($header, JSON_PRETTY_PRINT) . "\n";
    echo "Payload decoded: " . json_encode($decoded, JSON_PRETTY_PRINT) . "\n\n";
    
    // Step 4: Test expired token
    echo "4. Testing Expired Token...\n";
    $expiredPayload = [
        'user_id' => 123,
        'username' => 'john',
        'exp' => time() - 3600  // Already expired
    ];
    
    $expiredToken = SimpleJWT::encode($expiredPayload, JWT_SECRET);
    echo "Expired token created\n";
    
    try {
        SimpleJWT::decode($expiredToken, JWT_SECRET);
        echo "❌ This should not happen - expired token was accepted!\n";
    } catch (Exception $e) {
        echo "✅ Expired token correctly rejected: " . $e->getMessage() . "\n\n";
    }
    
    // Step 5: Test invalid signature
    echo "5. Testing Invalid Signature...\n";
    $tamperedToken = $token . 'tampered';
    
    try {
        SimpleJWT::decode($tamperedToken, JWT_SECRET);
        echo "❌ This should not happen - tampered token was accepted!\n";
    } catch (Exception $e) {
        echo "✅ Tampered token correctly rejected: " . $e->getMessage() . "\n\n";
    }
    
    echo "=== All Tests Completed Successfully! ===\n";
    echo "Token is valid for " . ($decoded['exp'] - time()) . " more seconds.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
