<?php
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
| API routes return JSON data for applications (mobile apps, frontend frameworks).
| URLs: http://yourapp.com/api/student, http://yourapp.com/api/hello
|
*/

use App\Http\Controllers\Api\StudentController;
use Illuminate\Support\Facades\Route;

// Simple student API routes (returns JSON data)
Route::get('/student', [StudentController::class, 'show']);
Route::get('/student/{id}', [StudentController::class, 'showById']);
Route::post('/student', [StudentController::class, 'store']);
Route::put('/student', [StudentController::class, 'update']);
Route::delete('/student', [StudentController::class, 'destroy']);

// Simple example API routes for practice
Route::get('/hello', [StudentController::class, 'hello']);
Route::get('/time', [StudentController::class, 'time']);
Route::post('/greet', [StudentController::class, 'greet']);
