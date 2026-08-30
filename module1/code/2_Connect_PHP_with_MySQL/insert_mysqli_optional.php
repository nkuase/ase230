<?php
// OPTIONAL: MySQLi version of the first INSERT example (MySQL-specific alternative).
// The required course examples use PDO; see index_pdo.php.

$conn = new mysqli("localhost", "ase230", "ase230pass", "studentdb");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully<br>";

$name = "Student 1";
$email = "student1@example.com";
$age = 20;
$major = "Computer Science";
$year = 2;

$sql = "INSERT INTO students (name, email, age, major, year)
        VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssisi", $name, $email, $age, $major, $year);
$stmt->execute();

$studentId = $conn->insert_id;
echo "Created Student 1 with ID {$studentId}";

$stmt->close();
$conn->close();
