<?php

use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

// The home page goes to the student list
Route::redirect('/', '/students');

// One line creates all seven CRUD routes (run `php artisan route:list --path=students`):
// GET    /students                  students.index     list
// GET    /students/create           students.create    form
// POST   /students                  students.store     save
// GET    /students/{student}        students.show      details
// GET    /students/{student}/edit   students.edit      form
// PUT    /students/{student}        students.update    save
// DELETE /students/{student}        students.destroy   delete
Route::resource('students', StudentController::class);
