<?php
declare(strict_types=1);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['error' => 'Use POST']);
    exit;
}

$file = $_FILES['document'] ?? null;
if (!is_array($file)) {
    http_response_code(400);
    echo json_encode(['error' => 'Upload a file in the document field']);
    exit;
}

if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'File upload failed', 'upload_error' => $file['error']]);
    exit;
}

$content = file_get_contents($file['tmp_name']);
if ($content === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not read the uploaded file']);
    exit;
}

// Note: Returning raw file content in JSON works for UTF-8 text (e.g., README.md).
// Binary files (images, PDFs) can fail in json_encode() due to invalid UTF-8 bytes
// and should be saved directly using move_uploaded_file() or base64-encoded.
echo json_encode([
    'description' => $_POST['description'] ?? '',
    'content' => $content,
    'file' => [
        'name' => $file['name'],
        'size' => $file['size'],
        'type' => $file['type'],
        'error' => $file['error'],
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
