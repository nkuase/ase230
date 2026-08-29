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

$method = $_SERVER['REQUEST_METHOD'];

// Only allow GET requests for this simple educational API
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Only POST requests are allowed in this simple API', 405);
    exit;
}

switch ($method) {
    case 'POST':
        // Handle POST parameters
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $info = [
            'name' => $name,
            'email' => $email
        ];
        sendResponse($info, 'Response from POST request');
        break;

    default:
        sendError('Method Not Supported', 404);
        break;
}
?>
