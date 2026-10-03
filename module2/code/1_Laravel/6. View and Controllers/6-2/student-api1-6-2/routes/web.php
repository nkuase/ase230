<?php

use App\Http\Controllers\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
| Web routes: they return HTML pages (routes/api.php returns JSON).
| {student} is route model binding: Laravel finds the Student or returns 404.
*/

Route::get('/', function () {
    return '<h1>Student Management (Web + API)</h1>
            <h3>Web pages (HTML)</h3>
            <ul>
                <li><a href="/students">View all students</a></li>
                <li><a href="/students/create">Add a new student</a></li>
                <li><a href="/students/year/2">Year 2 students</a></li>
                <li><a href="/hello">Hello</a> | <a href="/time">Time</a> | <a href="/greet">Greet form</a></li>
            </ul>
            <h3>API (JSON)</h3>
            <ul>
                <li><a href="/api/students">All students</a></li>
                <li><a href="/api/students/stats">Statistics</a></li>
                <li><a href="/api/students/search?name=John">Search</a></li>
            </ul>';
});

Route::get('/students', [StudentController::class, 'index'])->name('students.index');
Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
Route::post('/students', [StudentController::class, 'store'])->name('students.store');
Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');

Route::get('/hello', [StudentController::class, 'hello']);
Route::get('/time', [StudentController::class, 'time']);

// Route constraint: only years 1-4 match
Route::get('/students/year/{year}', function ($year) {
    $html = "<h1>Year {$year} Students</h1>";
    foreach (\App\Models\Student::where('year', $year)->get() as $student) {
        $html .= '<p>' . e($student->name) . ' - ' . e($student->major) . '</p>';
    }
    return $html . '<a href="/students">Back to All Students</a>';
})->where('year', '[1-4]');

// A form that posts to a route: csrf_field() is required here too
Route::get('/greet', function () {
    return '<h1>Greet Someone</h1>
            <p>Students in database: ' . \App\Models\Student::count() . '</p>
            <form method="POST" action="/greet">' . csrf_field() . '
                <p>Name: <input type="text" name="name" placeholder="Enter your name"></p>
                <p><button type="submit">Say Hello</button></p>
            </form>
            <a href="/">Back to Home</a>';
});

Route::post('/greet', function (Request $request) {
    $name = $request->name ?? 'Anonymous';

    return '<h1>Hello, ' . e($name) . '!</h1>
            <p>We currently have ' . \App\Models\Student::count() . ' students in our database.</p>
            <a href="/greet">Greet again</a> | <a href="/">Back to Home</a>';
});
