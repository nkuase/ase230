<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

/**
 * Sample students (same data as Lesson 4).
 * firstOrCreate() skips emails that already exist, so rerunning is safe.
 */
class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $students = [
            ['name' => 'John Doe',       'email' => 'john.doe@university.edu',       'age' => 20, 'major' => 'Computer Science', 'year' => 2],
            ['name' => 'Jane Smith',     'email' => 'jane.smith@university.edu',     'age' => 21, 'major' => 'Mathematics',      'year' => 3],
            ['name' => 'Bob Johnson',    'email' => 'bob.johnson@university.edu',    'age' => 19, 'major' => 'Physics',          'year' => 1],
            ['name' => 'Alice Brown',    'email' => 'alice.brown@university.edu',    'age' => 22, 'major' => 'Chemistry',        'year' => 4],
            ['name' => 'Charlie Wilson', 'email' => 'charlie.wilson@university.edu', 'age' => 20, 'major' => 'Computer Science', 'year' => 2],
            ['name' => 'Diana Davis',    'email' => 'diana.davis@university.edu',    'age' => 21, 'major' => 'Biology',          'year' => 3],
            ['name' => 'Eva Martinez',   'email' => 'eva.martinez@university.edu',   'age' => 19, 'major' => 'Engineering',      'year' => 1],
            ['name' => 'Frank Garcia',   'email' => 'frank.garcia@university.edu',   'age' => 23, 'major' => 'Mathematics',      'year' => 4],
        ];

        foreach ($students as $student) {
            Student::firstOrCreate(['email' => $student['email']], $student);
        }

        echo "Sample students ready: " . count($students) . "\n";
    }
}
