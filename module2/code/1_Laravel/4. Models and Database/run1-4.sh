#!/bin/bash
set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SOURCE_DIR="$SCRIPT_DIR/student-api1-4"
TARGET_DIR="$SCRIPT_DIR/student-api"
# Same database and account as Module 1 (crud_pdo.php)
DB_DATABASE="studentdb"
DB_USERNAME="ase230"
DB_PASSWORD="ase230pass"

if [[ ! -f "$TARGET_DIR/artisan" ]]; then
    composer create-project laravel/laravel "$TARGET_DIR"
fi
cd "$TARGET_DIR"

if [[ ! -f routes/api.php ]]; then
    php artisan install:api --no-interaction --without-migration-prompt
fi

if ! mysqladmin -h 127.0.0.1 ping >/dev/null 2>&1; then
    echo "MySQL is not running on 127.0.0.1:3306" >&2
    exit 1
fi

# WSL2/Linux: MySQL root uses socket authentication, so it needs sudo (as in Module 1).
# macOS (Homebrew): plain `mysql -u root` works; set MYSQL_ROOT_PASSWORD if root has a password.
if [[ "$(uname -s)" == "Linux" ]]; then
    MYSQL_ROOT=(sudo mysql -u root)
else
    MYSQL_ROOT=(env MYSQL_PWD="${MYSQL_ROOT_PASSWORD:-}" mysql -u root)
fi

if ! "${MYSQL_ROOT[@]}" <<SQL
CREATE DATABASE IF NOT EXISTS \`$DB_DATABASE\`;
CREATE USER IF NOT EXISTS '$DB_USERNAME'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON \`$DB_DATABASE\`.* TO '$DB_USERNAME'@'localhost';
SQL
then
    echo "Cannot configure MySQL as root. Linux/WSL2: check that 'sudo mysql' works. macOS: set MYSQL_ROOT_PASSWORD if root needs a password." >&2
    exit 1
fi

sed -E \
    -e 's/^DB_CONNECTION=.*/DB_CONNECTION=mysql/' \
    -e 's/^#? *DB_HOST=.*/DB_HOST=127.0.0.1/' \
    -e 's/^#? *DB_PORT=.*/DB_PORT=3306/' \
    -e "s/^#? *DB_DATABASE=.*/DB_DATABASE=$DB_DATABASE/" \
    -e "s/^#? *DB_USERNAME=.*/DB_USERNAME=$DB_USERNAME/" \
    -e "s/^#? *DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" \
    -e 's/^SESSION_DRIVER=.*/SESSION_DRIVER=file/' \
    -e 's/^CACHE_STORE=.*/CACHE_STORE=file/' \
    -e 's/^QUEUE_CONNECTION=.*/QUEUE_CONNECTION=sync/' \
    .env > .env.tmp
mv .env.tmp .env

mkdir -p app/Http/Controllers/Api
cp "$SOURCE_DIR/app/Http/Controllers/Api/StudentController.php" app/Http/Controllers/Api/StudentController.php
cp "$SOURCE_DIR/app/Models/Student.php" app/Models/Student.php
cp "$SOURCE_DIR/routes/api.php" routes/api.php
cp "$SOURCE_DIR/database/migrations/2024_01_01_000000_create_students_table.php" database/migrations/2024_01_01_000000_create_students_table.php
cp "$SOURCE_DIR/database/seeders/StudentSeeder.php" database/seeders/StudentSeeder.php

# Run only the students migration. Never use migrate:fresh here: studentdb is shared with Module 1.
php artisan migrate --path=database/migrations/2024_01_01_000000_create_students_table.php --force
php artisan db:seed --class=StudentSeeder --force
php artisan route:list --path=api

echo "Setup complete. Database: $DB_DATABASE (shared with Module 1)"
echo "To start server on port 8080:"
echo "  cd $TARGET_DIR"
echo "  php artisan serve --port=8080"
echo "  WSL2: php artisan serve --host=0.0.0.0 --port=8080"
echo "To test: curl http://localhost:8080/api/students"
