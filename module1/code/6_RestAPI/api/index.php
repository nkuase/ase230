<?php
/**
 * Simple Student Management REST API
 * 
 * This is a basic example for learning REST API concepts
 * 
 * Available endpoints:
 * GET    /students      - Get all students
 * GET    /students/{id} - Get student by ID
 * POST   /students      - Create new student
 * PUT    /students/{id} - Update student
 * DELETE /students/{id} - Delete student
 */

// Use JSON while learning the REST structure. Set STUDENT_STORAGE=pdo to
// reuse the Week 3 PDO pattern with MySQL for the Project 1 bridge.
$storage = getenv('STUDENT_STORAGE') ?: 'json';
require_once $storage === 'pdo'
    ? __DIR__ . '/handlers_pdo.php'
    : __DIR__ . '/handlers.php';

// Set JSON response header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Parse the URL path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($path, '/');

// Remove 'api' from path if present
// This is useful if the API is accessed via a prefix like /api/students
/* 
if (strpos($path, 'api/') === 0) {
    $path = substr($path, 4);
}
*/

$segments = explode('/', $path);
$resource = $segments[0] ?? ''; // students
$id = $segments[1] ?? null; // 123

// We can also use a more flexible approach to handle paths
// $path_parts = explode('/', trim($path, '/'));

// Get HTTP method
$method = $_SERVER['REQUEST_METHOD'];

// Simple routing
if (empty($resource)) {
    // Root endpoint - show API info
    echo json_encode([
        'message' => 'Simple Student Management API',
        'endpoints' => [
            'GET /students' => 'Get all students',
            'GET /students/{id}' => 'Get student by ID',
            'POST /students' => 'Create new student',
            'PUT /students/{id}' => 'Update student',
            'DELETE /students/{id}' => 'Delete student'
        ]
    ]);
    exit;
}

if ($resource === 'students') {
    $student_id = isset($id) ? (int)$id : null;
    
    switch ($method) {
        case 'GET':
            if ($student_id) {
                get_student($student_id);
            } else {
                get_all_students();
            }
            break;
            
        case 'POST':
            create_student();
            break;
            
        case 'PUT':
            if ($student_id) {
                update_student($student_id);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Student ID required']);
            }
            break;
            
        case 'DELETE':
            if ($student_id) {
                delete_student($student_id);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Student ID required']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Resource not found']);
}
