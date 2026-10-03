<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
     * Show student information (JSON response)
     * GET /api/student
     */
    public function show()
    {
        $student = $this->getStudent();
        
        return response()->json($student);
    }

    /**
     * Show student with specific ID
     * GET /api/student/{id}
     */
    public function showById($id)
    {
        return response()->json([
            'requested_id' => $id,
            'message' => 'You requested student with ID: ' . $id
        ]);
    }

    /**
     * Store new student (JSON response)
     * POST /api/student
     */
    public function store(Request $request)
    {
        $name = $request->name;
        $major = $request->major;
        
        return response()->json([
            'message' => 'Student created successfully',
            'student' => [
                'name' => $name,
                'major' => $major,
                'id' => 2  // Simulated new ID
            ]
        ]);
    }

    /**
     * Update student (JSON response)
     * PUT /api/student
     */
    public function update(Request $request)
    {
        return response()->json([
            'message' => 'Student updated successfully',
            'updated_name' => $request->name
        ]);
    }

    /**
     * Delete student (JSON response)
     * DELETE /api/student
     */
    public function destroy()
    {
        return response()->json([
            'message' => 'Student deleted successfully'
        ]);
    }

    /**
     * Simple greeting API
     * GET /api/hello
     */
    public function hello()
    {
        return response()->json([
            'message' => 'Hello World!',
            'type' => 'API Response'
        ]);
    }

    /**
     * Show current time (JSON)
     * GET /api/time
     */
    public function time()
    {
        return response()->json([
            'current_time' => date('Y-m-d H:i:s'),
            'timestamp' => time()
        ]);
    }

    /**
     * Greet user by name (JSON)
     * POST /api/greet
     */
    public function greet(Request $request)
    {
        $name = $request->name ?? 'Anonymous';
        
        return response()->json([
            'greeting' => 'Hello, ' . $name . '!',
            'message' => 'Nice to meet you!'
        ]);
    }
}
