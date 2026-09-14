<?php
/**
 * Simple CRUD Handlers for Student Management
 * 
 * These functions handle the basic Create, Read, Update, Delete operations
 * for students stored in a JSON file.
 */

require_once 'models/Student.php';

/**
 * Get all students
 */
function get_all_students()
{
    $students = load_students();
    echo json_encode([
        'success' => true,
        'data' => $students,
        'count' => count($students)
    ]);
}

/**
 * Get a specific student by ID
 */
function get_student($id)
{
    $students = load_students();

    foreach ($students as $student) {
        if ($student['id'] === $id) {
            echo json_encode([
                'success' => true,
                'data' => $student
            ]);
            return;
        }
    }

    http_response_code(404);
    echo json_encode([
        'success' => false,
        'error' => 'Student not found'
    ]);
}

/**
 * Shared value-validation for create and update, so a PUT can never save
 * data a POST would have rejected.
 */
function validate_student_fields($name, $email, $major, $year)
{
    if (!is_string($name) || trim($name) === '') {
        return 'name must be a non-empty string';
    }
    if (!is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'email is not a valid email address';
    }
    if (!is_string($major)) {
        return 'major must be a string';
    }
    if (!is_int($year) || $year < 1 || $year > 6) {
        return 'year must be an integer from 1 to 6';
    }
    return null;
}

/**
 * Create a new student
 */
function create_student()
{
    [$input, $is_object] = getRequestData();

    // Step 1: must be valid JSON, and specifically a JSON object
    if (!$is_object) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Body must be a valid JSON object']);
        return;
    }

    // Step 2: name and email must be present (their values are checked next)
    if (!array_key_exists('name', $input) || !array_key_exists('email', $input)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'name and email are required']);
        return;
    }

    // Step 3: validate the actual values — shared with update_student()
    $major = $input['major'] ?? '';
    $year = $input['year'] ?? 1;
    $error = validate_student_fields($input['name'], $input['email'], $major, $year);
    if ($error !== null) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $error]);
        return;
    }

    // Step 4: build and save the new student
    $students = load_students();
    $new_id = get_next_id($students);

    $new_student = new Student();
    $new_student->setId($new_id);
    $new_student->setName($input['name']);
    $new_student->setEmail($input['email']);
    $new_student->setMajor($major);
    $new_student->setYear($year);

    $students[] = $new_student->toArray();

    if (!save_students($students)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Could not save student data']);
        return;
    }

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Student created successfully',
        'data' => $new_student->toArray()
    ]);
}

/**
 * Update an existing student
 */
function update_student($id)
{
    [$input, $is_object] = getRequestData();
    if (!$is_object) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Body must be a valid JSON object']);
        return;
    }

    foreach (['name', 'email', 'major', 'year'] as $field) {
        if (!array_key_exists($field, $input)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "Missing required field: $field"]);
            return;
        }
    }

    $error = validate_student_fields($input['name'], $input['email'], $input['major'], $input['year']);
    if ($error !== null) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $error]);
        return;
    }

    $students = load_students();

    foreach ($students as $i => $student) {
        if ($student['id'] === $id) {
            $students[$i]['name']       = trim($input['name']);
            $students[$i]['email']      = trim($input['email']);
            $students[$i]['major']      = trim($input['major']);
            $students[$i]['year']       = $input['year'];
            $students[$i]['updated_at'] = date('Y-m-d H:i:s');

            if (!save_students($students)) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Could not save student data']);
                return;
            }

            echo json_encode([
                'success' => true,
                'message' => 'Student updated successfully',
                'data' => $students[$i]
            ]);
            return;
        }
    }

    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Student not found']);
}

/**
 * Delete a student
 */
function delete_student($id)
{
    $students = load_students();

    // Find and remove student
    for ($i = 0; $i < count($students); $i++) {
        if ($students[$i]['id'] === $id) {
            $deleted_student = $students[$i];
            array_splice($students, $i, 1);

            if (!save_students($students)) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Could not save student data']);
                return;
            }

            echo json_encode([
                'success' => true,
                'message' => 'Student deleted successfully',
                'data' => $deleted_student
            ]);
            return;
        }
    }

    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Student not found']);
}

/**
 * Load students from JSON file
 */
function load_students()
{
    $file_path = 'data/students.json';

    if (!file_exists($file_path)) {
        return [];
    }

    $json_data = file_get_contents($file_path);
    if ($json_data === false) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Could not read student data file']);
        exit;
    }

    $students = json_decode($json_data, true);
    if (!is_array($students) || !array_is_list($students)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Student data file is corrupted']);
        exit;
    }

    return $students;
}

/**
 * Save students to JSON file
 */
function save_students($students)
{
    $dir = 'data';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $file_path = $dir . '/students.json';
    $json_data = json_encode($students, JSON_PRETTY_PRINT);
    if ($json_data === false) {
        return false;
    }
    $bytes_written = file_put_contents($file_path, $json_data);
    return $bytes_written !== false;
}

/**
 * Get the request data from the input stream
 * 
 * This function reads the raw input data and reports whether it was syntactically valid JSON.
 */
function getRequestData()
{
    $raw = file_get_contents('php://input');
    $decoded = json_decode($raw);           // object mode first
    $is_object = is_object($decoded);
    // json_decode(..., true) turns BOTH objects and arrays into PHP arrays,
    // so we only re-decode as an associative array once we know it's an object.
    $data = $is_object ? json_decode($raw, true) : null;
    return [$data, $is_object];
}

/**
 * Get the next available ID
 */
function get_next_id($students)
{
    $max_id = 0;
    foreach ($students as $student) {
        if ($student['id'] > $max_id) {
            $max_id = $student['id'];
        }
    }
    return $max_id + 1;
}
