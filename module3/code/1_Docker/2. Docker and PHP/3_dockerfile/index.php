<?php
// api.php
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $response = [
        'message' => 'Hello from Docker PHP!',
        'php_version' => PHP_VERSION,
        'timestamp' => time(),
        'server' => 'Docker Container'
    ];
    echo json_encode($response, JSON_PRETTY_PRINT);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>