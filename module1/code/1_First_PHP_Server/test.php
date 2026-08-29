<?php
declare(strict_types=1);

/**
 * OPTIONAL — not part of the required Week 2 material.
 *
 * This file is not covered in Simple_PHP_Server.md, PHP_syntax.md, or
 * PHP_OOP.md, and it is not tested in the Questions files or the weekly
 * quizzes. You do not need to run it to complete this week's work.
 *
 * What it's for: if you ever install Xdebug and want to confirm it's
 * working (see "4. Debugging_Optional.md" for more on that), running
 * this file will show you its configuration. Use it only when you
 * actually need to check Xdebug — otherwise you can ignore this file.
 *
 * Development-only PHP/Xdebug configuration check.
 *
 * Do not expose this file on a public or production web server because the
 * displayed configuration can contain sensitive environment information.
 */

if (function_exists('xdebug_info')) {
  xdebug_info();
  exit;
}

header('Content-Type: text/plain; charset=utf-8');
http_response_code(200);

echo "Xdebug is not installed or enabled.\n";
echo "PHP itself is working correctly.\n";
echo "This optional example requires Xdebug for detailed debugging information.\n";
