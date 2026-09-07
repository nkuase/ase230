<?php
/**
 * Front controller for the frontend JavaScript examples.
 * Every API request enters through this file and is routed by method and path.
 */

// Enable error reporting for development (helps students debug)
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Send a JSON response and stop execution.
 * All other response helpers funnel through this function.
 *
 * @param array $payload Data to encode as JSON
 * @param int $code HTTP status code
 */
function sendJson(array $payload, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Send a successful JSON response
 *
 * @param mixed $data Data to include in response
 * @param string $message Success message
 * @param int $code HTTP status code
 */
function sendResponse($data, string $message = 'Success', int $code = 200): void {
    $count = is_countable($data) ? count($data) : 1;
    sendJson([
        'success' => true,
        'message' => $message,
        'data'    => $data,
        'count'   => $count,
    ], $code);
}

/**
 * Send an error JSON response
 *
 * @param string $message Error message
 * @param int $code HTTP status code
 */
function sendError(string $message, int $code = 400): void {
    sendJson([
        'success' => false,
        'message' => $message,
        'data'    => null,
    ], $code);
}

// Only URLs beginning with /index.php/ are valid API URLs.
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$frontController = '/index.php/';

if (!str_starts_with($requestPath, $frontController)) {
    sendError('Endpoint not found', 404);
}

// Convert an external URL such as /index.php/api into the internal route "api".
$path = substr($requestPath, strlen($frontController));
$method = $_SERVER['REQUEST_METHOD'];

// Route every endpoint from this one front controller.
switch ($path) {
    case 'api':
        if ($method !== 'GET') {
            sendError('Method not allowed for this endpoint', 405);
        }

        // API information endpoint
        $info = [
            'name' => 'Simple Student Management API',
            'version' => '1.0',
            'description' => 'A minimal API for learning PHP basics with student data',
            'endpoints' => [
                'GET /index.php/api' => 'Show this API information',
                'POST /index.php/submit' => 'Process submitted form data',
            ]
        ];
        sendResponse($info, 'Welcome to Simple Student Management API');
        break;

    case 'submit':
        if ($method !== 'POST') {
            sendError('Method not allowed for this endpoint', 405);
        }

        // Support both FormData and JSON request bodies.
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (stripos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $name = $input['name'] ?? '';
            $email = $input['email'] ?? '';
        } else {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
        }

        sendResponse([
            'name' => $name,
            'email' => $email,
        ], 'Response from POST request');
        break;

    default:
        sendError('Endpoint not found', 404);
        break;
}
