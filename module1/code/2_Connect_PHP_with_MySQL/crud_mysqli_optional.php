<?php
// OPTIONAL: MySQLi CRUD reference (MySQL-specific alternative).
// The required course example is crud_pdo.php.

$conn = new mysqli("localhost", "ase230", "ase230pass", "studentdb");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>Optional MySQLi CRUD Example</h1>";
echo "Connected successfully<br>";

// CREATE
$sql = "INSERT INTO students (name, email, age, major, year)
        VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

$name = "Alice Johnson";
$email = "alice.johnson@example.com";
$age = 22;
$major = "Computer Science";
$year = 3;
$stmt->bind_param("ssisi", $name, $email, $age, $major, $year);
$stmt->execute();
$aliceId = $conn->insert_id;

$name = "Bob Smith";
$email = "bob.smith@example.com";
$age = 21;
$major = "Mathematics";
$year = 2;
$stmt->execute();
$bobId = $conn->insert_id;
$stmt->close();

echo "<h2>CREATE</h2>";
echo "Created Alice with ID {$aliceId}<br>";
echo "Created Bob with ID {$bobId}<br>";

// READ one
$sql = "SELECT id, name, email, age, major, year
        FROM students WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $aliceId);
$stmt->execute();

// get_result() requires the mysqlnd driver, included in standard course installs.
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

echo "<h2>READ</h2>";
echo "Found {$student['name']} with ID {$student['id']}<br>";

// UPDATE
$major = "Computer Engineering";
$year = 4;
$sql = "UPDATE students SET major = ?, year = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $major, $year, $aliceId);
$stmt->execute();
$stmt->close();

echo "<h2>UPDATE</h2>";
echo "Updated Alice with ID {$aliceId}<br>";

// DELETE
$sql = "DELETE FROM students WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $bobId);
$stmt->execute();
$stmt->close();

echo "<h2>DELETE</h2>";
echo "Deleted Bob with ID {$bobId}<br>";

// READ final results
$result = $conn->query(
    "SELECT id, name, email, age, major, year FROM students"
);

echo "<h2>Final Results</h2>";
while ($student = $result->fetch_assoc()) {
    echo "ID: {$student['id']} - Name: {$student['name']}"
       . " - Major: {$student['major']} - Year: {$student['year']}<br>";
}

$conn->close();
