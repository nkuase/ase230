<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
| Web routes return simple HTML strings for human users browsing with browsers.
| URLs: http://yourapp.com/student, http://yourapp.com/hello
|
*/

use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

// Welcome page
Route::get('/', function () {
    return '<h1>Welcome to Laravel Routes Demo</h1>
            <h2>Try these links:</h2>
            <ul>
                <li><a href="/student">Student Info (Web)</a></li>
                <li><a href="/hello">Hello World</a></li>
                <li><a href="/time">Current Time</a></li>
                <li><a href="/api/student">Student Info (API)</a></li>
            </ul>';
});

// Simple student routes (returns HTML strings)
Route::get('/student', [StudentController::class, 'show']);
Route::get('/student/create', [StudentController::class, 'create']);
Route::get('/student/{id}', [StudentController::class, 'showById']);
Route::post('/student', [StudentController::class, 'store']);
Route::put('/student', [StudentController::class, 'update']);
Route::delete('/student', [StudentController::class, 'destroy']);

// Simple example routes for practice
Route::get('/hello', [StudentController::class, 'hello']);
Route::get('/time', [StudentController::class, 'time']);
Route::post('/greet', [StudentController::class, 'greet']);

// Simple form to test greet
Route::get('/greet', function() {
    return '<h1>Greet Someone</h1>
            <form method="POST" action="/greet">' . csrf_field() . '
                <p>Name: <input type="text" name="name" placeholder="Enter your name"></p>
                <p><button type="submit">Say Hello</button></p>
            </form>
            <a href="/">Back to Home</a>';
});
