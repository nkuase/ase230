<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create Students Table (shared with Module 1)
 *
 * Module 1 already creates this table in the 'studentdb' database:
 *
 *   CREATE TABLE IF NOT EXISTS students (
 *     id INT AUTO_INCREMENT PRIMARY KEY,
 *     name VARCHAR(100),
 *     email VARCHAR(150),
 *     age INT,
 *     major VARCHAR(100),
 *     year INT
 *   );
 *
 * Teaching Points:
 * 1. Migrations = Database Version Control
 * 2. Blueprint = Table Structure Definition
 * 3. Schema = Database Schema Management
 * 4. up() = Create/Modify, down() = Rollback
 * 5. Schema::hasTable() makes the migration safe when the table already exists
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This method defines what happens when we run:
     * php artisan migrate --path=database/migrations/2024_01_01_000000_create_students_table.php
     */
    public function up(): void
    {
        // Module 1 may have created the table already. Never overwrite its data.
        if (Schema::hasTable('students')) {
            return;
        }

        // Same columns as the Module 1 CREATE TABLE statement (no timestamps, no unique index).
        Schema::create('students', function (Blueprint $table) {
            $table->integer('id', true);            // INT AUTO_INCREMENT PRIMARY KEY
            $table->string('name', 100)->nullable();  // VARCHAR(100)
            $table->string('email', 150)->nullable(); // VARCHAR(150)
            $table->integer('age')->nullable();       // INT
            $table->string('major', 100)->nullable(); // VARCHAR(100)
            $table->integer('year')->nullable();      // INT - academic year (1-4)
        });
    }

    /**
     * Reverse the migrations.
     *
     * WARNING: The table is shared with Module 1. Rolling back deletes every student row.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
