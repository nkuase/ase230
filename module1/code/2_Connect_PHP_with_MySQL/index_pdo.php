<?php
// First PDO connection and INSERT example.

$dsn = "mysql:host=localhost;dbname=studentdb;charset=utf8mb4";
$pdo = new PDO($dsn, "ase230", "ase230pass", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

echo "Connected successfully<br>";

$sql = "INSERT INTO students (name, email, age, major, year)
        VALUES (:name, :email, :age, :major, :year)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':name' => 'Student 1',
    ':email' => 'student1@example.com',
    ':age' => 20,
    ':major' => 'Computer Science',
    ':year' => 2,
]);

$studentId = (int) $pdo->lastInsertId();
echo "Created Student 1 with ID {$studentId}";
