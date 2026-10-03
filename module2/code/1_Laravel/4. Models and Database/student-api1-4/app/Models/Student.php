<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Student Model - Represents the 'students' table in the 'studentdb' database
 *
 * This is the SAME table that Module 1 (crud_pdo.php) reads and writes with PDO.
 *
 * Key Learning Points:
 * 1. Model = Database Table Representation
 * 2. $timestamps = false: the Module 1 table has no created_at / updated_at columns
 * 3. $fillable = Mass Assignment Protection
 * 4. $casts = Data Type Conversion
 * 5. Eloquent ORM provides powerful query methods
 */
class Student extends Model
{
    // The table has no created_at / updated_at columns, so tell Eloquent not to use them.
    public $timestamps = false;

    protected $fillable = [
        'name',
        'email',
        'age',
        'major',
        'year'
    ];

    protected $casts = [
        'age' => 'integer',
        'year' => 'integer',
    ];
}
