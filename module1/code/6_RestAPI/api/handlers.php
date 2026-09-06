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
        if ($student['id'] == $id) {
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
 * Create a new student
 */
function create_student()
{
    // Get JSON input
    $input = getRequestData();

    if (!$input) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid JSON data'
        ]);
        return;
    }

    // Load existing students
    $students = load_students();

    // Generate new ID
    $new_id = get_next_id($students);

    // Create new student
    $new_student = new Student();
    $new_student->setId($new_id);
    $new_student->setName($input['name'] ?? '');
    $new_student->setEmail($input['email'] ?? '');
    $new_student->setMajor($input['major'] ?? '');
    $new_student->setYear($input['year'] ?? 1);

    // Add to students array
    $students[] = $new_student->toArray();

    // Save to file
    save_students($students);

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
    // Get JSON input
    $input = getRequestData();

    if (!$input) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid JSON data'
        ]);
        return;
    }

    // Load students
    $students = load_students();

    // Find and update student
    for ($i = 0; $i < count($students); $i++) {
        if ($students[$i]['id'] == $id) {
            // Update fields if provided
            if (isset($input['name']))
                $students[$i]['name'] = $input['name'];
            if (isset($input['email']))
                $students[$i]['email'] = $input['email'];
            if (isset($input['major']))
                $students[$i]['major'] = $input['major'];
            if (isset($input['year']))
                $students[$i]['year'] = $input['year'];

            // Update timestamp
            $students[$i]['updated_at'] = date('Y-m-d H:i:s');

            // Save to file
            save_students($students);

            echo json_encode([
                'success' => true,
                'message' => 'Student updated successfully',
                'data' => $students[$i]
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
 * Delete a student
 */
function delete_student($id)
{
    $students = load_students();

    // Find and remove student
    for ($i = 0; $i < count($students); $i++) {
        if ($students[$i]['id'] == $id) {
            $deleted_student = $students[$i];
            array_splice($students, $i, 1);

            // Save to file
            save_students($students);

            echo json_encode([
                'success' => true,
                'message' => 'Student deleted successfully',
                'data' => $deleted_student
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
 * Load students from JSON file
 */
function load_students()
{
    $file_path = 'data/students.json';

    if (!file_exists($file_path)) {
        return [];
    }

    $json_data = file_get_contents($file_path);
    $students = json_decode($json_data, true);

    return $students ?: [];
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
    file_put_contents($file_path, $json_data);
}

/**
 * Get the request data from the input stream
 * 
 * This function reads the raw input data and decodes it as JSON.
 * If decoding fails, it returns an empty array.
 */
function getRequestData()
{
    $input = file_get_contents('php://input');
    return json_decode($input, true) ?? [];
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
