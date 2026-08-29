<?php
/**
 * Simple PHP API for Students Management
 * Educational project focusing on basic PHP and API concepts
 * 
 * This API provides simple GET endpoints for managing student data.
 * Perfect for learning PHP basics without complex features.
 */

// Enable error reporting for development (helps students debug)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type to JSON for all responses
header('Content-Type: application/json');

/**
 * Simple function to send successful JSON response
 * 
 * @param mixed $data Data to include in response
 * @param string $message Success message
 */
function sendResponse($data, $message = 'Success') {
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data,
        'count' => is_array($data) ? count($data) : 1
    ], JSON_PRETTY_PRINT);
}

/**
 * Simple function to send error response
 * 
 * @param string $message Error message
 * @param int $code HTTP status code
 */
function sendError($message, $code = 400) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $message,
        'data' => null
    ], JSON_PRETTY_PRINT);
}

// Get the request path and clean it up
$path = $_SERVER['REQUEST_URI']; // 
$path = parse_url($path, PHP_URL_PATH);
$path = trim($path, '/');

// Remove index.php from path if present
if (strpos($path, 'index.php') === 0) {
    $path = substr($path, 9);
    $path = trim($path, '/');
}

// Only allow GET requests for this simple educational API
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Only GET requests are allowed in this simple API', 405);
    exit;
}

// Route the request using a switch statement
switch ($path) {
    case 'api':
        // API information endpoint
        $info = [
            'name' => 'Simple Student Management API',
            'version' => '1.0',
            'description' => 'A minimal API for learning PHP basics with student data',
            'endpoints' => [
                'GET /api' => 'Show this API information',
            ]
        ];
        sendResponse($info, 'Welcome to Simple Student Management API');
        break;
    default:
        sendError('Endpoint not found', 404);
        break;
}
?>
