<?php
header('Content-Type: application/json');

echo json_encode([
    'message' => 'Hello from PHP Container!',
    'php_version' => phpversion(),
    'timestamp' => date('c'),
    'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'container' => gethostname()
], JSON_PRETTY_PRINT);
?>
