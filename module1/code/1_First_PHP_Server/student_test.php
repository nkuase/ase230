<?php
declare(strict_types=1);

require_once __DIR__ . '/Student.php';

$s1 = new Student(1, 'Alice Johnson', 'alice@university.edu');

// Demonstrate method access, property access, and object-to-array conversion.
$response = [
  'greeting' => $s1->greet(),
  'email' => $s1->email,
  'student' => $s1->toArray(),
];

header('Content-Type: application/json; charset=utf-8');

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
