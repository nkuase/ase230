<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

/**
 * Student API Controller (same actions as Lesson 4)
 *
 * The controller does not check tokens. The routes do:
 * reading is public, writing sits behind auth:sanctum in routes/api.php.
 */
class StudentController extends Controller
{
    // GET /api/students (public)
    public function index()
    {
        $students = Student::all();

        return response()->json([
            'success' => true,
            'message' => 'Students retrieved successfully',
            'data' => $students,
            'total' => $students->count(),
        ]);
    }

    // GET /api/students/{student} (public)
    public function show(Student $student)
    {
        return response()->json([
            'success' => true,
            'message' => 'Student retrieved successfully',
            'data' => $student,
        ]);
    }

    // POST /api/students (protected)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:students',
            'age' => 'required|integer|min:1|max:120',
            'major' => 'required|string|max:100',
            'year' => 'required|integer|min:1|max:4',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Student created successfully',
            'data' => Student::create($validated),
        ], 201);
    }

    // PUT /api/students/{student} (protected)
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'email' => 'sometimes|required|email|max:150|unique:students,email,' . $student->id,
            'age' => 'sometimes|required|integer|min:1|max:120',
            'major' => 'sometimes|required|string|max:100',
            'year' => 'sometimes|required|integer|min:1|max:4',
        ]);

        $student->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Student updated successfully',
            'data' => $student->fresh(),
        ]);
    }

    // DELETE /api/students/{student} (protected)
    public function destroy(Student $student)
    {
        $student->delete();

        return response()->json([
            'success' => true,
            'message' => 'Student deleted successfully',
        ]);
    }
}
