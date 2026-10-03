<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Student Model - the same 'students' table in 'studentdb' as Module 1 and Lesson 4.
 */
class Student extends Model
{
    // The table has no created_at / updated_at columns
    public $timestamps = false;

    protected $fillable = ['name', 'email', 'age', 'major', 'year'];

    protected $casts = [
        'age' => 'integer',
        'year' => 'integer',
    ];
}
