<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Student Web Controller - returns HTML strings (the Api controller returns JSON)
 *
 * e() escapes text that comes from the user, so <script> is shown as text and does not run.
 * csrf_field() adds the hidden token that every web form needs.
 */
class StudentController extends Controller
{
    /**
     * Simple student data - just one student for learning
     */
    private function getStudent()
    {
        return [
            'id' => 1,
            'name' => 'John Doe',
            'major' => 'Computer Science'
        ];
    }

    /**
     * Show student information (HTML response)
     * GET /student
     */
    public function show()
    {
        $student = $this->getStudent();
        
        return '<h1>Student Information</h1>
                <p>ID: ' . $student['id'] . '</p>
                <p>Name: ' . $student['name'] . '</p>
                <p>Major: ' . $student['major'] . '</p>
                <a href="/student/create">Add New Student</a>';
    }

    /**
     * Show student with specific ID
     * GET /student/{id}
     */
    public function showById($id)
    {
        return '<h1>Student ID: ' . e($id) . '</h1>
                <p>You requested student with ID: ' . e($id) . '</p>
                <a href="/student">Back to Student</a>';
    }

    /**
     * Show form to create new student
     * GET /student/create
     */
    public function create()
    {
        return '<h1>Add New Student</h1>
                <form method="POST" action="/student">' . csrf_field() . '
                    <p>Name: <input type="text" name="name" required></p>
                    <p>Major: <input type="text" name="major" required></p>
                    <p><button type="submit">Create Student</button></p>
                </form>
                <a href="/student">Cancel</a>';
    }

    /**
     * Store new student
     * POST /student
     */
    public function store(Request $request)
    {
        $name = $request->name;
        $major = $request->major;
        
        return '<h1>Success!</h1>
                <p>Student created successfully!</p>
                <p>Name: ' . e($name) . '</p>
                <p>Major: ' . e($major) . '</p>
                <a href="/student">View Student</a> | 
                <a href="/student/create">Add Another</a>';
    }

    /**
     * Update student
     * PUT /student
     */
    public function update(Request $request)
    {
        return '<h1>Updated!</h1>
                <p>Student information updated</p>
                <p>New name: ' . e($request->name) . '</p>
                <a href="/student">View Student</a>';
    }

    /**
     * Delete student
     * DELETE /student
     */
    public function destroy()
    {
        return '<h1>Deleted!</h1>
                <p>Student has been removed</p>
                <a href="/student/create">Add New Student</a>';
    }

    /**
     * Simple greeting example
     * GET /hello
     */
    public function hello()
    {
        return '<h1>Hello World!</h1>
                <p>This is a simple Laravel controller response</p>';
    }

    /**
     * Show current time
     * GET /time
     */
    public function time()
    {
        return '<h1>Current Time</h1>
                <p>' . date('Y-m-d H:i:s') . '</p>
                <a href="/hello">Go to Hello</a>';
    }

    /**
     * Greet user by name
     * POST /greet
     */
    public function greet(Request $request)
    {
        $name = $request->name ?? 'Anonymous';
        
        return '<h1>Hello, ' . e($name) . '!</h1>
                <p>Nice to meet you!</p>
                <form method="POST" action="/greet">' . csrf_field() . '
                    <p>Name: <input type="text" name="name" placeholder="Enter your name"></p>
                    <p><button type="submit">Greet Again</button></p>
                </form>';
    }
}
