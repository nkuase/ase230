<?php
declare(strict_types=1);

/**
 * PHP Syntax Core Demo (ASE 230 - Module 1)
 *
 * This script demonstrates the key syntax features covered in "2. PHP_syntax.md".
 * Run in terminal: php syntax_demo.php
 */

if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
}

echo "========================================\n";
echo "  ASE 230: PHP Syntax Core Demonstration\n";
echo "========================================\n\n";

// --------------------------------------------------
// 1. Variables & Types
// --------------------------------------------------
echo "--- 1. Variables & Types ---\n";
$course = "ASE 230"; // string
$count  = 3;         // int
$ratio  = 3.14;      // float
$isOk   = true;      // bool
$nil    = null;      // null

echo "Course: $course (type: " . gettype($course) . ")\n";
echo "Count: $count (type: " . gettype($count) . ")\n";
echo "Ratio: $ratio (type: " . gettype($ratio) . ")\n";
echo "isOk: " . ($isOk ? 'true' : 'false') . " (type: " . gettype($isOk) . ")\n";
echo "nil: " . var_export($nil, true) . " (type: " . gettype($nil) . ")\n\n";

// --------------------------------------------------
// 2. Constants & Enums (PHP 8.1+)
// --------------------------------------------------
echo "--- 2. Constants & Enums ---\n";
const APP_NAME = 'MyApp';
echo "Constant APP_NAME: " . APP_NAME . "\n";

enum Status: string {
    case Draft = 'draft';
    case Published = 'published';
}

$postStatus = Status::Draft;
echo "Enum value: " . $postStatus->value . "\n";

// Match expression with Enum
$statusMessage = match ($postStatus) {
    Status::Draft     => "Work in progress",
    Status::Published => "Publicly visible",
};
echo "Match result: $statusMessage\n\n";

// --------------------------------------------------
// 3. Strings & Interpolation
// --------------------------------------------------
echo "--- 3. Strings & Interpolation ---\n";
$lang = "PHP";
echo "Double quotes (interpolated): Hello $lang\n";
echo 'Single quotes (literal): Hello $lang' . "\n";

$heredoc = <<<TXT
Heredoc multi-line with variable: $lang
TXT;
echo $heredoc . "\n\n";

// --------------------------------------------------
// 4. Arrays (Indexed & Associative) & DTO
// --------------------------------------------------
echo "--- 4. Arrays & DTO ---\n";
$colors = ['red', 'green', 'blue'];     // indexed array
$user   = ['id' => 7, 'name' => 'Ada']; // associative array

$colors[] = 'purple';                   // push
[$first, $second] = $colors;            // destructuring
$merged = [...$colors, 'black'];        // spread operator (PHP 7.4+)

echo "First color: $first, Second color: $second\n";
echo "Merged colors: " . implode(', ', $merged) . "\n";
echo "User name from array: " . $user['name'] . "\n";

// Data Transfer Object (DTO)
final class UserDto {
    public function __construct(
        public int $id,
        public string $name,
    ) {}
}

$dtoUser = new UserDto(id: 7, name: 'Ada');
echo "DTO User -> ID: {$dtoUser->id}, Name: {$dtoUser->name}\n\n";

// --------------------------------------------------
// 5. Control Flow: If/Elseif/Else, Ternary, Null Coalescing
// --------------------------------------------------
echo "--- 5. Control Flow ---\n";
$score = 87;
if ($score >= 90) {
    $grade = 'A';
} elseif ($score >= 80) {
    $grade = 'B';
} else {
    $grade = 'C';
}
$label = ($score >= 60) ? 'pass' : 'fail'; // ternary

$queryParam = null;
$username = $queryParam ?? 'guest'; // null coalescing

echo "Score: $score -> Grade: $grade ($label)\n";
echo "Username with fallback: $username\n\n";

// --------------------------------------------------
// 6. Functions (Value, Reference, Variadics, Arrow)
// --------------------------------------------------
echo "--- 6. Functions ---\n";

// 6-1. Pass by Value (Default)
function incVal(int $x): void {
    $x++;
}
$val = 1;
incVal($val);
echo "Pass by value: \$val = $val (unchanged)\n";

// 6-2. Pass by Reference
function incRef(int &$x): void {
    $x++;
}
$ref = 1;
incRef($ref);
echo "Pass by reference: \$ref = $ref (changed to 2)\n";

// 6-3. Variadic Arguments
function sumAll(int ...$nums): int {
    return array_sum($nums);
}
echo "Variadic sumAll(1, 2, 3, 4): " . sumAll(1, 2, 3, 4) . "\n";

// 6-4. Closure (Multi-line anonymous function)
$triple = function (int $n): int {
    return $n * 3;
};
echo "Closure triple(5): " . $triple(5) . "\n";

// 6-5. Arrow Function (Single-line expression)
$double = fn(int $n): int => $n * 2;
echo "Arrow function double(5): " . $double(5) . "\n";

echo "\n========================================\n";
echo "  All syntax demonstrations completed!  \n";
echo "========================================\n";
