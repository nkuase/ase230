<?php
// Complete CRUD example for the core PDO learning path.

$dsn = "mysql:host=localhost;dbname=studentdb;charset=utf8mb4";
$pdo = new PDO($dsn, "ase230", "ase230pass", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

echo "<h1>PDO CRUD Example</h1>";
echo "Connected successfully<br>";

// CREATE
echo "<h2>CREATE</h2>";
$sql = "INSERT INTO students (name, email, age, major, year)
        VALUES (:name, :email, :age, :major, :year)";
$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':name' => 'Alice Johnson',
    ':email' => 'alice.johnson@example.com',
    ':age' => 22,
    ':major' => 'Computer Science',
    ':year' => 3,
]);
$aliceId = (int) $pdo->lastInsertId();
echo "Created Alice with ID {$aliceId}<br>";

$stmt->execute([
    ':name' => 'Bob Smith',
    ':email' => 'bob.smith@example.com',
    ':age' => 21,
    ':major' => 'Mathematics',
    ':year' => 2,
]);
$bobId = (int) $pdo->lastInsertId();
echo "Created Bob with ID {$bobId}<br>";

// READ all
echo "<h2>READ: All Students</h2>";
$sql = "SELECT id, name, email, age, major, year FROM students";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$students = $stmt->fetchAll();

foreach ($students as $student) {
    echo "ID: {$student['id']} - Name: {$student['name']}"
       . " - Major: {$student['major']} - Year: {$student['year']}<br>";
}

// READ one
echo "<h2>READ: Alice</h2>";
$sql = "SELECT id, name, email, age, major, year
        FROM students
        WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $aliceId]);
$student = $stmt->fetch();

if ($student) {
    echo "Found {$student['name']} with ID {$student['id']}<br>";
} else {
    echo "Student not found<br>";
}

// UPDATE
echo "<h2>UPDATE</h2>";
$sql = "UPDATE students
        SET major = :major, year = :year
        WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':major' => 'Computer Engineering',
    ':year' => 4,
    ':id' => $aliceId,
]);
$updatedRows = $stmt->rowCount();
echo "Updated Alice with ID {$aliceId}<br>";

// DELETE
echo "<h2>DELETE</h2>";
$sql = "DELETE FROM students WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $bobId]);
$deletedRows = $stmt->rowCount();
echo "Deleted Bob with ID {$bobId}<br>";

// READ final results
echo "<h2>Final Results</h2>";
$sql = "SELECT id, name, email, age, major, year FROM students";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$students = $stmt->fetchAll();

foreach ($students as $student) {
    echo "ID: {$student['id']} - Name: {$student['name']}"
       . " - Major: {$student['major']} - Year: {$student['year']}<br>";
}

// Row counts
echo "<h2>Row Counts</h2>";
echo "Updated rows: {$updatedRows}<br>";
echo "Deleted rows: {$deletedRows}<br>";
