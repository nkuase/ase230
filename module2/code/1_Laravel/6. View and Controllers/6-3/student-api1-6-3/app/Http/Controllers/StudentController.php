<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

/**
 * Student Web Controller - returns Blade views
 *
 * Lesson 4 (optional): return '<h1>...</h1>' . $html;   (HTML strings inside the controller)
 * Lesson 6:            return view('students.index', compact('students'));
 *
 * The controller gets the data. The view decides how it looks.
 */
class StudentController extends Controller
{
    // GET /students
    public function index()
    {
        return view('students.index', ['students' => Student::all()]);
    }

    // GET /students/create
    public function create()
    {
        return view('students.create');
    }

    // POST /students
    public function store(Request $request)
    {
        // On a validation error, Laravel redirects back with $errors and old input
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:students',
            'age' => 'required|integer|min:1|max:120',
            'major' => 'required|string|max:100',
            'year' => 'required|integer|min:1|max:4',
        ]);

        Student::create($validated);

        // Redirect, then show the message once (a "flash" message)
        return redirect()->route('students.index')->with('success', 'Student created.');
    }

    // GET /students/{student}  (route model binding: Laravel finds the student or returns 404)
    public function show(Student $student)
    {
        return view('students.show', compact('student'));
    }

    // GET /students/{student}/edit
    public function edit(Student $student)
    {
        return view('students.edit', compact('student'));
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

        return redirect()->route('students.show', $student)->with('success', 'Student updated.');
    }

    // DELETE /students/{student}
    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()->route('students.index')->with('success', 'Student deleted.');
    }
}
