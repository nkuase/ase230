<?php
/**
 * Simple JWT Implementation for Educational Purposes
 * DO NOT use in production - use firebase/php-jwt instead
 */
class SimpleJWT {
    
    public static function encode($payload, $secret) {
        // Create header
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
        // Encode header and payload
        $headerEncoded = self::base64UrlEncode($header);
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));
        
        // Create signature
        $signature = hash_hmac('sha256', $headerEncoded . "." . $payloadEncoded, $secret, true);
        $signatureEncoded = self::base64UrlEncode($signature);
        
        // Return JWT token
        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }
    
    public static function decode($jwt, $secret) {
        // Split the JWT
        $parts = explode('.', $jwt);
        
        if (count($parts) != 3) {
            throw new Exception('Invalid JWT format');
        }
        
        list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;
        
        // Verify signature
        $expectedSignature = hash_hmac('sha256', $headerEncoded . "." . $payloadEncoded, $secret, true);
        $providedSignature = self::base64UrlDecode($signatureEncoded);
        
        if (!hash_equals($expectedSignature, $providedSignature)) {
            throw new Exception('Invalid signature');
        }
        
        // Decode payload
        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);
        
        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new Exception('Token has expired');
        }
        
        return $payload;
    }
    
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    private static function base64UrlDecode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}
?>
