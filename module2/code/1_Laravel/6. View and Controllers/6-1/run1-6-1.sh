#!/bin/bash
set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SOURCE_DIR="$SCRIPT_DIR/student-api1-6-1"        # HTML files (this lesson)
LESSON3_DIR="$SCRIPT_DIR/../../3. Routes and Controllers/student-api1-3"    # API files (same as Lesson 3)
TARGET_DIR="$SCRIPT_DIR/student-api"

if [[ ! -f "$LESSON3_DIR/routes/api.php" ]]; then
    echo "Lesson 3 files not found: $LESSON3_DIR" >&2
    exit 1
fi

if [[ ! -f "$TARGET_DIR/artisan" ]]; then
    composer create-project laravel/laravel "$TARGET_DIR"
fi
cd "$TARGET_DIR"

if [[ ! -f routes/api.php ]]; then
    php artisan install:api --no-interaction --without-migration-prompt
fi

mkdir -p app/Http/Controllers/Api
# API: the same finished files as Lesson 3
cp "$LESSON3_DIR/app/Http/Controllers/Api/StudentController.php" app/Http/Controllers/Api/StudentController.php
cp "$LESSON3_DIR/routes/api.php" routes/api.php
# HTML: the web controller and the web routes
cp "$SOURCE_DIR/app/Http/Controllers/StudentController.php" app/Http/Controllers/StudentController.php
cp "$SOURCE_DIR/routes/web.php" routes/web.php

php artisan route:list --except-vendor

echo "Setup complete."
echo "To start server on port 8080:"
echo "  cd $TARGET_DIR"
echo "  php artisan serve --port=8080"
echo "  WSL2: php artisan serve --host=0.0.0.0 --port=8080"
echo "To test in a browser: http://localhost:8080/"
echo "To test the API:      curl http://localhost:8080/api/hello"
