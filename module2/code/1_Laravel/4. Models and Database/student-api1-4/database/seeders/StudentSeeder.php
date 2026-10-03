<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

/**
 * Student Database Seeder
 * 
 * Teaching Points:
 * 1. Seeders provide sample data for testing and development
 * 2. Demonstrate Model::create() with multiple records
 * 3. Show realistic student data for university system
 * 4. firstOrCreate() skips emails that already exist, so the shared
 *    studentdb.students table (also used by Module 1) is never duplicated
 * 
 * Usage:
 * php artisan db:seed --class=StudentSeeder
 * OR
 * php artisan db:seed (if registered in DatabaseSeeder)
 */
class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample students data
        $students = [
            [
                'name' => 'John Doe',
                'email' => 'john.doe@university.edu',
                'age' => 20,
                'major' => 'Computer Science',
                'year' => 2
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@university.edu',
                'age' => 21,
                'major' => 'Mathematics',
                'year' => 3
            ],
            [
                'name' => 'Bob Johnson',
                'email' => 'bob.johnson@university.edu',
                'age' => 19,
                'major' => 'Physics',
                'year' => 1
            ],
            [
                'name' => 'Alice Brown',
                'email' => 'alice.brown@university.edu',
                'age' => 22,
                'major' => 'Chemistry',
                'year' => 4
            ],
            [
                'name' => 'Charlie Wilson',
                'email' => 'charlie.wilson@university.edu',
                'age' => 20,
                'major' => 'Computer Science',
                'year' => 2
            ],
            [
                'name' => 'Diana Davis',
                'email' => 'diana.davis@university.edu',
                'age' => 21,
                'major' => 'Biology',
                'year' => 3
            ],
            [
                'name' => 'Eva Martinez',
                'email' => 'eva.martinez@university.edu',
                'age' => 19,
                'major' => 'Engineering',
                'year' => 1
            ],
            [
                'name' => 'Frank Garcia',
                'email' => 'frank.garcia@university.edu',
                'age' => 23,
                'major' => 'Mathematics',
                'year' => 4
            ]
        ];

        // Create students in database
        foreach ($students as $studentData) {
            Student::firstOrCreate(['email' => $studentData['email']], $studentData);
        }

        // Alternative method: Using Model::insert() for bulk insertion
        // Student::insert($students); // Note: This won't trigger model events or fill timestamps
        
        echo "Sample students ready: " . count($students) . "\n";
    }
}
