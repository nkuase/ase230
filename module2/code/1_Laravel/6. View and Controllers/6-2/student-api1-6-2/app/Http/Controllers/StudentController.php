<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

/**
 * Student Web Controller - returns HTML pages (the API controller returns JSON)
 *
 * Same 'students' table as the API: columns id, name, email, age, major, year.
 * Two things every HTML form needs in Laravel:
 * 1. csrf_field(): a hidden token. Without it, POST/PUT/DELETE returns 419 Page Expired.
 * 2. e(): escapes user input, so a name like <script> is shown as text, not run.
 */
class StudentController extends Controller
{
    // GET /students
    public function index()
    {
        $html = '<h1>All Students</h1><a href="/students/create">Add New Student</a><hr>';

        foreach (Student::all() as $student) {
            $html .= '<div style="border: 1px solid #ccc; margin: 10px; padding: 10px;">'
                . '<h3>' . e($student->name) . '</h3>'
                . '<p>Email: ' . e($student->email) . '</p>'
                . '<p>Age: ' . e($student->age) . '</p>'
                . '<p>Major: ' . e($student->major) . '</p>'
                . '<p>Year: ' . e($student->year) . '</p>'
                . '<a href="/students/' . $student->id . '">View Details</a> | '
                . '<a href="/students/' . $student->id . '/edit">Edit</a>'
                . '</div>';
        }

        return $html;
    }

    // GET /students/create
    public function create()
    {
        return '<h1>Add New Student</h1>
                <form method="POST" action="/students">' . csrf_field() . '
                    <p>Name: <input type="text" name="name" required></p>
                    <p>Email: <input type="email" name="email" required></p>
                    <p>Age: <input type="number" name="age" min="1" max="120" required></p>
                    <p>Major: <input type="text" name="major" required></p>
                    <p>Year: <input type="number" name="year" min="1" max="4" required></p>
                    <p><button type="submit">Create Student</button></p>
                </form>
                <a href="/students">Cancel</a>';
    }

    // POST /students
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:students',
            'age' => 'required|integer|min:1|max:120',
            'major' => 'required|string|max:100',
            'year' => 'required|integer|min:1|max:4',
        ]);

        $student = Student::create($validated);

        return '<h1>Success!</h1>
                <p>Student created: ' . e($student->name) . ' (ID ' . $student->id . ')</p>
                <a href="/students">View All Students</a> |
                <a href="/students/create">Add Another</a>';
    }

    // GET /students/{student}  (route model binding: Laravel finds the student or returns 404)
    public function show(Student $student)
    {
        return '<h1>Student Details</h1>
                <p><strong>ID:</strong> ' . $student->id . '</p>
                <p><strong>Name:</strong> ' . e($student->name) . '</p>
                <p><strong>Email:</strong> ' . e($student->email) . '</p>
                <p><strong>Age:</strong> ' . e($student->age) . '</p>
                <p><strong>Major:</strong> ' . e($student->major) . '</p>
                <p><strong>Year:</strong> ' . e($student->year) . '</p>
                <hr>
                <a href="/students">Back to All Students</a> |
                <a href="/students/' . $student->id . '/edit">Edit Student</a> |
                <form method="POST" action="/students/' . $student->id . '" style="display: inline;">'
                . csrf_field() . '
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" onclick="return confirm(\'Are you sure?\')">Delete</button>
                </form>';
    }

    // GET /students/{student}/edit
    public function edit(Student $student)
    {
        return '<h1>Edit Student: ' . e($student->name) . '</h1>
                <form method="POST" action="/students/' . $student->id . '">' . csrf_field() . '
                    <input type="hidden" name="_method" value="PUT">
                    <p>Name: <input type="text" name="name" value="' . e($student->name) . '" required></p>
                    <p>Email: <input type="email" name="email" value="' . e($student->email) . '" required></p>
                    <p>Age: <input type="number" name="age" min="1" max="120" value="' . e($student->age) . '" required></p>
                    <p>Major: <input type="text" name="major" value="' . e($student->major) . '" required></p>
                    <p>Year: <input type="number" name="year" min="1" max="4" value="' . e($student->year) . '" required></p>
                    <p><button type="submit">Update Student</button></p>
                </form>
                <a href="/students/' . $student->id . '">Cancel</a>';
    }

    // PUT /students/{student}
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:students,email,' . $student->id,
            'age' => 'required|integer|min:1|max:120',
            'major' => 'required|string|max:100',
            'year' => 'required|integer|min:1|max:4',
        ]);

        $student->update($validated);

        return '<h1>Updated!</h1>
                <p>' . e($student->name) . ' was updated.</p>
                <a href="/students/' . $student->id . '">View Student</a> |
                <a href="/students">All Students</a>';
    }

    // DELETE /students/{student}
    public function destroy(Student $student)
    {
        $name = $student->name;
        $student->delete();

        return '<h1>Deleted!</h1>
                <p>Student "' . e($name) . '" has been removed from the database.</p>
                <a href="/students">View All Students</a> |
                <a href="/students/create">Add New Student</a>';
    }

    // GET /hello
    public function hello()
    {
        return '<h1>Hello World!</h1>
                <p>Total students in database: ' . Student::count() . '</p>
                <a href="/students">View Students</a>';
    }

    // GET /time
    public function time()
    {
        return '<h1>Current Time</h1>
                <p>' . date('Y-m-d H:i:s') . '</p>
                <p>Students in database: ' . Student::count() . '</p>
                <a href="/students">View Students</a>';
    }
}
