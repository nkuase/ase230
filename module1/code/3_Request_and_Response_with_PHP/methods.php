<?php
declare(strict_types=1);

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
if (!in_array($method, ['POST', 'PUT'], true)) {
    http_response_code(405);
    header('Allow: POST, PUT');
    echo json_encode(['error' => 'Use POST or PUT']);
    exit;
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$rawBody = file_get_contents('php://input');
$data = str_contains($contentType, 'application/json')
    ? json_decode($rawBody, true)
    : $_POST;

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Request body must contain valid JSON']);
    exit;
}

echo json_encode([
    'method' => $method,
    'content_type' => $contentType,
    'data' => $data,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
