<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Same migration as Lesson 4: the students table is shared with Module 1.
 * WARNING: down() deletes every student row.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Module 1 may have created the table already. Never overwrite its data.
        if (Schema::hasTable('students')) {
            return;
        }

        Schema::create('students', function (Blueprint $table) {
            $table->integer('id', true);              // INT AUTO_INCREMENT PRIMARY KEY
            $table->string('name', 100)->nullable();
            $table->string('email', 150)->nullable();
            $table->integer('age')->nullable();
            $table->string('major', 100)->nullable();
            $table->integer('year')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
