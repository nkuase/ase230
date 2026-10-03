<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Course;
use App\Models\Student;
use Illuminate\Database\Seeder;

class OrmPracticeSeeder extends Seeder
{
    public function run(): void
    {
        $student = Student::updateOrCreate(
            ['email' => 'practice@example.com'],
            ['name' => 'Practice Student', 'major' => 'CS', 'year' => 2, 'is_active' => true]
        );
        Student::updateOrCreate(
            ['email' => 'inactive@example.com'],
            ['name' => 'Inactive Student', 'major' => 'Math', 'year' => 1, 'is_active' => false]
        );
        $courseIds = [];
        foreach (['ASE230' => 'Server-Side Programming', 'CSC260' => 'Object-Oriented Programming'] as $code => $title) {
            $courseIds[] = Course::updateOrCreate(['code' => $code], ['title' => $title])->id;
        }
        $student->courses()->sync($courseIds);

        // Five fictional authors make the N+1 query-count example repeatable.
        for ($number = 1; $number <= 5; $number++) {
            $author = Author::firstOrCreate(['name' => "Practice Author $number"]);
            foreach (['First Book', 'Second Book'] as $title) {
                $author->books()->firstOrCreate(['title' => $title]);
            }
        }
    }
}
