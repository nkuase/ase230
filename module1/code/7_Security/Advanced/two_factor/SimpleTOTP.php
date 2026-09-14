<?php
/**
 * Simple TOTP (Time-based One-Time Password) Implementation
 * Based on RFC 6238 for educational purposes
 */
class SimpleTOTP {
    
    /**
     * Generate a random secret for 2FA
     */
    public static function generateSecret($length = 16) {
        return random_bytes($length);
    }
    
    /**
     * Generate TOTP code from secret and timestamp
     */
    public static function generateCode($secret, $timestamp = null) {
        if ($timestamp === null) {
            $timestamp = time();
        }
        
        // TOTP uses 30-second time windows
        $time_slice = floor($timestamp / 30);
        
        // Create time-based counter (8 bytes, big-endian)
        $counter = pack('N*', 0) . pack('N*', $time_slice);
        
        // Generate HMAC-SHA1 hash
        $hash = hash_hmac('sha1', $counter, $secret, true);
        
        // Dynamic truncation (RFC 4226)
        $offset = ord($hash[19]) & 0xf;
        $code = (
            ((ord($hash[$offset+0]) & 0x7f) << 24) |
            ((ord($hash[$offset+1]) & 0xff) << 16) |
            ((ord($hash[$offset+2]) & 0xff) << 8) |
            (ord($hash[$offset+3]) & 0xff)
        ) % 1000000;
        
        // Return 6-digit code with leading zeros
        return sprintf('%06d', $code);
    }
    
    /**
     * Verify TOTP code against secret
     * Allows 1 time window tolerance for clock drift
     */
    public static function verifyCode($code, $secret, $timestamp = null) {
        if ($timestamp === null) {
            $timestamp = time();
        }
        
        // Check current time window and ±1 window for clock drift
        for ($i = -1; $i <= 1; $i++) {
            $test_time = $timestamp + ($i * 30);
            $expected_code = self::generateCode($secret, $test_time);
            
            if (hash_equals($code, $expected_code)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Convert binary secret to Base32 for QR codes
     */
    public static function base32Encode($data) {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $encoded = '';
        $bits = '';
        
        for ($i = 0; $i < strlen($data); $i++) {
            $bits .= sprintf('%08b', ord($data[$i]));
        }
        
        // Pad to multiple of 5 bits
        while (strlen($bits) % 5 !== 0) {
            $bits .= '0';
        }
        
        // Convert every 5 bits to base32 character
        for ($i = 0; $i < strlen($bits); $i += 5) {
            $chunk = substr($bits, $i, 5);
            $encoded .= $alphabet[bindec($chunk)];
        }
        
        return $encoded;
    }
    
    /**
     * Generate QR code URL for authenticator apps
     */
    public static function getQRCodeURL($secret, $username, $issuer = 'Demo App') {
        $secret_base32 = self::base32Encode($secret);
        $label = urlencode($issuer . ':' . $username);
        $params = http_build_query([
            'secret' => $secret_base32,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => 6,
            'period' => 30
        ]);
        
        $otpauth_url = "otpauth://totp/{$label}?{$params}";
        
        // Option 1: QR Server API (Free, reliable)
        return "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($otpauth_url);
        
        // Option 2: QuickChart (Free tier available)
        // return "https://quickchart.io/qr?text=" . urlencode($otpauth_url) . "&size=200";
        
        // Option 3: QR Code Generator API
        // return "http://api.qrserver.com/v1/create-qr-code/?color=000000&bgcolor=FFFFFF&data=" . urlencode($otpauth_url) . "&qzone=1&margin=0&size=200x200&ecc=L";
    }
    
    /**
     * Generate multiple QR code URLs for fallback options
     * Useful for educational purposes to show different services
     */
    public static function getQRCodeURLs($secret, $username, $issuer = 'Demo App') {
        $secret_base32 = self::base32Encode($secret);
        $label = urlencode($issuer . ':' . $username);
        $params = http_build_query([
            'secret' => $secret_base32,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => 6,
            'period' => 30
        ]);
        
        $otpauth_url = "otpauth://totp/{$label}?{$params}";
        
        return [
            'qr_server' => "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($otpauth_url),
            'quickchart' => "https://quickchart.io/qr?text=" . urlencode($otpauth_url) . "&size=200",
            'otpauth_url' => $otpauth_url,
            'manual_key' => $secret_base32
        ];
    }
}
?>
