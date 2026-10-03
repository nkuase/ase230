# ORM practice

**Lessons 1 and 2 (Eloquent Basics):** reuse Laravel lesson 4’s generated `student-api/` (`run1-4.sh` + `student-api1-4/`). Run `php artisan tinker` there.

**Optional lessons 3-1 and 3-2:** use this separate reference project with its **own practice database and schema** (timestamps, unique email, GPA, and more). It differs from lesson 4's `studentdb` table on purpose, so nothing here changes your Module 1 data.

## Run the optional example

Use the same PHP, Composer, and MySQL tools as the Laravel lessons.

1. Copy `run2-optional.sh` and `orm-reference/` together into a fresh practice directory.
2. Start MySQL. Edit the database settings at the top of the copied script. Use a **new practice database name**; if the account already exists, enter its current password.
3. Run:

```bash
bash run2-optional.sh
cd orm-practice
php artisan tinker
```

The script creates the MySQL database, copies the models and migrations, and seeds sample data. The models cover authors/books and students/courses; the migrations include GPA and a `major`/`year` index. Inspect these files instead of creating duplicate migrations.

## Check in Tinker

```php
use App\Models\{Author, Book, Student, Course};
use Illuminate\Support\Facades\DB;

Author::first()->books->pluck('title'); // First Book, Second Book
$student = Student::where('email', 'practice@example.com')->firstOrFail();
$student->courses->pluck('code');      // ASE230, CSC260
Student::inYear(2)->active()->get();    // Practice Student
$student->year;                       // integer 2
$student->is_active;                  // boolean true
```

Continue with the optional slides (3-1, 3-2) for relationship queries and GPA rollback. The script applies GPA last; check `php artisan migrate:status` before using `php artisan migrate:rollback --step=1`. Reapply with `php artisan migrate --step`.
