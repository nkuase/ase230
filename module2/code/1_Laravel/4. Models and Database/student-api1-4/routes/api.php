<?php
use App\Http\Controllers\Api\StudentController;
use Illuminate\Support\Facades\Route;

// Standard RESTful routes with Route Model Binding
Route::get('/students', [StudentController::class, 'index']);
Route::post('/students', [StudentController::class, 'store']);
Route::get('/students/major/{major}', [StudentController::class, 'getByMajor']);
Route::get('/students/year/{year}', [StudentController::class, 'getByYear'])->where('year', '[1-4]');
Route::get('/students/search', [StudentController::class, 'search']);
Route::get('/students/stats', [StudentController::class, 'stats']);
Route::get('/students/{student}', [StudentController::class, 'show']);
Route::put('/students/{student}', [StudentController::class, 'update']);
Route::delete('/students/{student}', [StudentController::class, 'destroy']);

// Simple example routes enhanced with database information
Route::get('/hello', [StudentController::class, 'hello']);
Route::get('/time', [StudentController::class, 'time']);
Route::post('/greet', [StudentController::class, 'greet']);

// Test endpoint to check if API is working
Route::get('/test', function() {
    return response()->json([
        'success' => true,
        'message' => 'API is working!',
        'lesson' => 'Models and Controllers',
        'database_connection' => 'OK',
        'database' => \Illuminate\Support\Facades\DB::connection()->getDatabaseName(), // studentdb (same as Module 1)
        'total_students' => \App\Models\Student::count(),
        'endpoints' => [
            'GET /api/students' => 'Get all students',
            'POST /api/students' => 'Create new student',
            'GET /api/students/{id}' => 'Get specific student',
            'PUT /api/students/{id}' => 'Update student',
            'DELETE /api/students/{id}' => 'Delete student',
            'GET /api/students/stats' => 'Database statistics',
            'GET /api/students/search?name=X' => 'Search students'
        ]
    ]);
});
