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
    $error = validate_pdo_student($input, true);
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
    http_response_code(201);
    get_student($id);
}

function update_student(int $id): void
{
    $input = get_pdo_request_data();
    $error = validate_pdo_student($input, false);
    if ($input === [] || $error !== null) {
        send_pdo_error($error ?? 'At least one field is required', 400);
        return;
    }

    $assignments = [];
    $parameters = [':id' => $id];
    foreach (['name', 'email', 'major', 'year'] as $field) {
        if (array_key_exists($field, $input)) {
            $assignments[] = "{$field} = :{$field}";
            $parameters[":{$field}"] = $field === 'year'
                ? (int)$input[$field]
                : trim($input[$field]);
        }
    }
    if ($assignments === []) {
        send_pdo_error('No supported fields were provided', 400);
        return;
    }

    $sql = 'UPDATE students SET ' . implode(', ', $assignments) .
        ', updated_at = CURRENT_TIMESTAMP WHERE id = :id';
    db()->prepare($sql)->execute($parameters);
    get_student($id);
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
    foreach (['name', 'email', 'major', 'year'] as $field) {
        if ($allRequired && !array_key_exists($field, $input)) {
            return "{$field} is required";
        }
    }
    if (isset($input['email']) && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        return 'A valid email is required';
    }
    if (isset($input['year']) && ((int)$input['year'] < 1 || (int)$input['year'] > 6)) {
        return 'Year must be between 1 and 6';
    }
    return null;
}

function send_pdo_error(string $message, int $status): void
{
    http_response_code($status);
    echo json_encode(['success' => false, 'error' => $message]);
}
