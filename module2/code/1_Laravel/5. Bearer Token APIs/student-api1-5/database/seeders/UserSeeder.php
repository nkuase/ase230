<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo users for login (the Module 1 bearer demo had `student` and `teacher`).
 * The User model hashes the password automatically ('password' => 'hashed').
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Student', 'email' => 'student@university.edu', 'password' => 'student123'],
            ['name' => 'Teacher', 'email' => 'teacher@university.edu', 'password' => 'teacher456'],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(['email' => $user['email']], $user);
        }

        echo "Demo users ready: " . count($users) . "\n";
    }
}
