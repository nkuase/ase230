<?php
declare(strict_types=1);

/** MySQL/PDO implementation of the same handler interface as handlers.php. */
require_once __DIR__ . '/database.php';

function get_all_students(): void
{
    $students = db()->query(
        'SELECT id, name, email, major, year, created_at, updated_at FROM students ORDER BY id'
    )->fetchAll();
    echo json_encode(['success' => true, 'data' => $students, 'count' => count($students)]);
}

function get_student(int $id): void
{
    $statement = db()->prepare(
        'SELECT id, name, email, major, year, created_at, updated_at FROM students WHERE id = :id'
    );
    $statement->execute([':id' => $id]);
    $student = $statement->fetch();

    if (!$student) {
        send_pdo_error('Student not found', 404);
        return;
    }
    echo json_encode(['success' => true, 'data' => $student]);
}

function create_student(): void
{
    $input = get_pdo_request_data();

    // Same POST contract as handlers.php: only name/email are required,
    // major/year default like the JSON version does.
    if (!array_key_exists('name', $input) || !array_key_exists('email', $input)) {
        send_pdo_error('name and email are required', 400);
        return;
    }
    $input['major'] = $input['major'] ?? '';
    $input['year'] = $input['year'] ?? 1;

    $error = validate_pdo_student($input, false);
    if ($error !== null) {
        send_pdo_error($error, 400);
        return;
    }

    $statement = db()->prepare(
        'INSERT INTO students (name, email, major, year) VALUES (:name, :email, :major, :year)'
    );
    $statement->execute([
        ':name' => trim($input['name']),
        ':email' => trim($input['email']),
        ':major' => trim($input['major']),
        ':year' => (int)$input['year'],
    ]);

    $id = (int)db()->lastInsertId();
    $statement = db()->prepare(
        'SELECT id, name, email, major, year, created_at, updated_at FROM students WHERE id = :id'
    );
    $statement->execute([':id' => $id]);
    $student = $statement->fetch();

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Student created successfully',
        'data' => $student,
    ]);
}

function update_student(int $id): void
{
    $input = get_pdo_request_data();
    $error = validate_pdo_student($input, true);
    if ($error !== null) {
        send_pdo_error($error, 400);
        return;
    }

    // PUT replaces the whole resource and validate_pdo_student(..., true)
    // already guarantees all four fields are present, so a fixed SET clause
    // is enough (no need to build it dynamically field-by-field).
    $statement = db()->prepare(
        'UPDATE students SET name = :name, email = :email, major = :major, year = :year, ' .
        'updated_at = CURRENT_TIMESTAMP WHERE id = :id'
    );
    $statement->execute([
        ':id' => $id,
        ':name' => trim($input['name']),
        ':email' => trim($input['email']),
        ':major' => trim($input['major']),
        ':year' => (int)$input['year'],
    ]);

    $statement = db()->prepare(
        'SELECT id, name, email, major, year, created_at, updated_at FROM students WHERE id = :id'
    );
    $statement->execute([':id' => $id]);
    $student = $statement->fetch();
    if (!$student) {
        send_pdo_error('Student not found', 404);
        return;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Student updated successfully',
        'data' => $student,
    ]);
}

function delete_student(int $id): void
{
    $statement = db()->prepare(
        'SELECT id, name, email, major, year, created_at, updated_at FROM students WHERE id = :id'
    );
    $statement->execute([':id' => $id]);
    $student = $statement->fetch();
    if (!$student) {
        send_pdo_error('Student not found', 404);
        return;
    }

    db()->prepare('DELETE FROM students WHERE id = :id')->execute([':id' => $id]);
    echo json_encode([
        'success' => true,
        'message' => 'Student deleted successfully',
        'data' => $student,
    ]);
}

function get_pdo_request_data(): array
{
    $decoded = json_decode(file_get_contents('php://input'), true);
    return is_array($decoded) ? $decoded : [];
}

function validate_pdo_student(array $input, bool $allRequired): ?string
{
    // Same contract as validate_student_fields() in handlers.php, so the
    // JSON and MySQL versions of the API accept and reject the same input.
    foreach (['name', 'email', 'major', 'year'] as $field) {
        if ($allRequired && !array_key_exists($field, $input)) {
            return "{$field} is required";
        }
    }
    if (array_key_exists('name', $input) && (!is_string($input['name']) || trim($input['name']) === '')) {
        return 'name must be a non-empty string';
    }
    if (array_key_exists('email', $input) && (!is_string($input['email']) || !filter_var($input['email'], FILTER_VALIDATE_EMAIL))) {
        return 'email is not a valid email address';
    }
    if (array_key_exists('major', $input) && !is_string($input['major'])) {
        return 'major must be a string';
    }
    if (array_key_exists('year', $input) && (!is_int($input['year']) || $input['year'] < 1 || $input['year'] > 6)) {
        return 'year must be an integer from 1 to 6';
    }
    return null;
}

function send_pdo_error(string $message, int $status): void
{
    http_response_code($status);
    echo json_encode(['success' => false, 'error' => $message]);
}
