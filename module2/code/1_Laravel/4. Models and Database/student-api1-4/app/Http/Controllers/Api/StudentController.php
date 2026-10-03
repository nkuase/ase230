<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

/**
 * Student API Controller - Using Laravel Models (Lesson 5)
 * 
 * Evolution from Lesson 4:
 * ❌ BEFORE: Used static arrays (mockup data)
 * ✅ NOW: Uses Laravel Model (real database operations)
 * 
 * Key Teaching Points:
 * 1. JSON API responses with proper HTTP status codes
 * 2. Route Model Binding for automatic record lookup
 * 3. Built-in validation with automatic error responses
 * 4. Consistent API response structure
 * 5. Same 'students' table (database 'studentdb') that Module 1 uses with PDO
 */
class StudentController extends Controller
{
    /**
     * Get all students
     * GET /api/students
     * 
     * EVOLUTION:
     * Before: return response()->json($this->getMockStudents());
     * Now: return response()->json(Student::all());
     */
    public function index()
    {
        $students = Student::all();
        
        return response()->json([
            'success' => true,
            'message' => 'Students retrieved successfully',
            'data' => $students,
            'total' => $students->count()
        ]);
    }

    /**
     * Create new student
     * POST /api/students
     * 
     * EVOLUTION:
     * Before: Manual array manipulation + simulated ID
     * Now: Real database creation with validation
     */
    public function store(Request $request)
    {
        // Validate request data
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:students',
            'age' => 'required|integer|min:1|max:120',
            'major' => 'required|string|max:100',
            'year' => 'required|integer|min:1|max:4'
        ]);

        // Create student in database
        $student = Student::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Student created successfully',
            'data' => $student
        ], 201); // 201 = Created
    }

    /**
     * Get specific student
     * GET /api/students/{student}
     * 
     * EVOLUTION - Route Model Binding:
     * Before: Manual ID lookup: $student = $this->findStudentById($id)
     * Now: Laravel automatically finds student or returns 404
     */
    public function show(Student $student)
    {
        return response()->json([
            'success' => true,
            'message' => 'Student retrieved successfully',
            'data' => $student
        ]);
    }

    /**
     * Update student
     * PUT /api/students/{student}
     * 
     * EVOLUTION:
     * Before: Manual array search and update
     * Now: Eloquent model update with validation
     */
    public function update(Request $request, Student $student)
    {
        // Validate request data (sometimes = only validate if field is present)
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'email' => 'sometimes|required|email|max:150|unique:students,email,' . $student->id,
            'age' => 'sometimes|required|integer|min:1|max:120',
            'major' => 'sometimes|required|string|max:100',
            'year' => 'sometimes|required|integer|min:1|max:4'
        ]);

        // Update student in database
        $student->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Student updated successfully',
            'data' => $student->fresh() // fresh() reloads from database
        ]);
    }

    /**
     * Delete student
     * DELETE /api/students/{student}
     * 
     * EVOLUTION:
     * Before: Manual array filtering
     * Now: Eloquent model deletion
     */
    public function destroy(Student $student)
    {
        $student->delete();

        return response()->json([
            'success' => true,
            'message' => 'Student deleted successfully'
        ]);
    }

    /**
     * Advanced API endpoints demonstrating Eloquent capabilities
     */

    /**
     * Get students by major
     * GET /api/students/major/{major}
     */
    public function getByMajor($major)
    {
        $students = Student::where('major', $major)->get();

        return response()->json([
            'success' => true,
            'message' => "Students in {$major} retrieved successfully",
            'data' => $students,
            'total' => $students->count()
        ]);
    }

    /**
     * Get students by year
     * GET /api/students/year/{year}
     */
    public function getByYear($year)
    {
        $students = Student::where('year', $year)->get();

        return response()->json([
            'success' => true,
            'message' => "Year {$year} students retrieved successfully",
            'data' => $students,
            'total' => $students->count()
        ]);
    }

    /**
     * Search students by name
     * GET /api/students/search?name=john
     */
    public function search(Request $request)
    {
        $name = $request->query('name');
        
        if (!$name) {
            return response()->json([
                'success' => false,
                'message' => 'Name parameter is required'
            ], 400);
        }

        $students = Student::where('name', 'like', '%' . $name . '%')->get();

        return response()->json([
            'success' => true,
            'message' => "Search results for '{$name}'",
            'data' => $students,
            'total' => $students->count()
        ]);
    }

    /**
     * Get database statistics
     * GET /api/students/stats
     */
    public function stats()
    {
        $totalStudents = Student::count();
        $studentsByYear = [];
        $studentsByMajor = [];

        // Count students by year
        for ($year = 1; $year <= 4; $year++) {
            $studentsByYear["year_{$year}"] = Student::where('year', $year)->count();
        }

        // Count students by major
        $majors = Student::distinct('major')->pluck('major');
        foreach ($majors as $major) {
            $studentsByMajor[$major] = Student::where('major', $major)->count();
        }

        return response()->json([
            'success' => true,
            'message' => 'Database statistics retrieved successfully',
            'data' => [
                'total_students' => $totalStudents,
                'students_by_year' => $studentsByYear,
                'students_by_major' => $studentsByMajor,
                // The table has no created_at column, so order by id instead
                'latest_student' => Student::latest('id')->first(),
                'oldest_student' => Student::oldest('id')->first()
            ]
        ]);
    }

    /**
     * Simple example methods (keeping from Lesson 4 for comparison)
     */
    public function hello()
    {
        return response()->json([
            'message' => 'Hello World!',
            'type' => 'API Response with Database Info',
            'total_students' => Student::count(),
            'latest_student' => Student::latest('id')->first()
        ]);
    }

    public function time()
    {
        return response()->json([
            'current_time' => date('Y-m-d H:i:s'),
            'timestamp' => time(),
            'total_students' => Student::count()
        ]);
    }

    public function greet(Request $request)
    {
        $name = $request->name ?? 'Anonymous';
        
        return response()->json([
            'greeting' => 'Hello, ' . $name . '!',
            'message' => 'Nice to meet you!',
            'total_students_in_database' => Student::count()
        ]);
    }
}
