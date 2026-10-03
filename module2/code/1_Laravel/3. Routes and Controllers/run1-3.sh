#!/bin/bash
set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SOURCE_DIR="$SCRIPT_DIR/student-api1-3"
TARGET_DIR="$SCRIPT_DIR/student-api"

if [[ ! -f "$TARGET_DIR/artisan" ]]; then
    composer create-project laravel/laravel "$TARGET_DIR"
fi
cd "$TARGET_DIR"

if [[ ! -f routes/api.php ]]; then
    php artisan install:api --no-interaction --without-migration-prompt
fi

mkdir -p app/Http/Controllers/Api
cp "$SOURCE_DIR/app/Http/Controllers/Api/StudentController.php" app/Http/Controllers/Api/StudentController.php
cp "$SOURCE_DIR/routes/api.php" routes/api.php

php artisan route:list --path=api

echo "Setup complete."
echo "To start server on port 8080:"
echo "  cd $TARGET_DIR"
echo "  php artisan serve --port=8080"
echo "  WSL2: php artisan serve --host=0.0.0.0 --port=8080"
echo "To test: curl http://localhost:8080/api/hello"
