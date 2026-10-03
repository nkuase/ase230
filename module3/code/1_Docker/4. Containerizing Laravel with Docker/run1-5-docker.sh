#!/bin/bash
# Docker version of Module 2's run1-5.sh:  bash run1-5-docker.sh
#
# Same steps, same names:
#   student-api1-5/  -> the lesson files (identical to Module 2)
#   student-api/     -> the Laravel project this script creates
# Difference: nginx, php, and mysql run in three containers, so there is no
# `sudo mysql`, no `php artisan serve`, and nothing to install except Docker.
set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SOURCE_DIR="$SCRIPT_DIR/student-api1-5"
TARGET_DIR="$SCRIPT_DIR/student-api"
cd "$SCRIPT_DIR"

# Run a command inside a throw-away php container (working dir = the Laravel project).
php_run() { docker compose run --rm --no-deps -T -e COMPOSER_ALLOW_SUPERUSER=1 php "$@"; }

docker compose config --quiet
docker compose build

if [[ ! -f "$TARGET_DIR/artisan" ]]; then
    docker compose run --rm --no-deps -T -e COMPOSER_ALLOW_SUPERUSER=1 -v "$SCRIPT_DIR:/work" -w /work php \
        composer create-project laravel/laravel student-api "^13.0" --no-scripts
fi

# Database settings: .env.docker (same database and account as Module 1 and 2, host = mysql)
if [[ ! -f "$TARGET_DIR/.env" ]]; then
    docker compose run --rm --no-deps -T -v "$SCRIPT_DIR/.env.docker:/tmp/env.docker:ro" php \
        cp /tmp/env.docker .env
fi

php_run composer install --no-interaction --no-scripts
php_run php artisan package:discover
if ! grep -q '^APP_KEY=base64:' "$TARGET_DIR/.env"; then
    php_run php artisan key:generate --force
fi

# install:api installs Laravel Sanctum (config + personal_access_tokens migration)
if [[ ! -f "$TARGET_DIR/config/sanctum.php" ]]; then
    php_run php artisan install:api --no-interaction --without-migration-prompt
fi

# Copy the lesson files (same list as Module 2's run1-5.sh); done inside the container
# because student-api/ was created there.
docker compose run --rm --no-deps -T -v "$SOURCE_DIR:/source:ro" php sh -c '
    mkdir -p app/Http/Controllers/Api
    for f in \
        app/Http/Controllers/Api/AuthController.php \
        app/Http/Controllers/Api/StudentController.php \
        app/Models/Student.php \
        app/Models/User.php \
        routes/api.php \
        database/migrations/2024_01_01_000000_create_students_table.php \
        database/seeders/StudentSeeder.php \
        database/seeders/UserSeeder.php
    do
        cp "/source/$f" "$f"
    done'

# The bind mount hides permissions set in the image: let PHP-FPM (www-data) write here.
php_run chown -R www-data:www-data storage bootstrap/cache

# Start nginx + php + mysql and wait until all three are healthy.
docker compose up -d --wait
docker compose exec -T php php artisan config:clear

# Run only the migrations we need, as in Module 2.
# install:api names the token migration with today's timestamp. If student-api/ is created
# again while the mysql volume still holds the old tables, the table exists but this file
# is new to Laravel. Skip a table that already exists instead of failing with error 1050.
table_exists() {
    docker compose exec -T -e T="$1" mysql sh -c \
        'mysql -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" -N -e "SHOW TABLES LIKE \"$T\"" "$MYSQL_DATABASE" 2>/dev/null' | grep -q .
}

TOKENS_MIGRATION=$(ls "$TARGET_DIR"/database/migrations/*_create_personal_access_tokens_table.php | head -1)
TOKENS_MIGRATION="database/migrations/$(basename "$TOKENS_MIGRATION")"
docker compose exec -T php php artisan migrate --path=database/migrations/2024_01_01_000000_create_students_table.php --force
table_exists users || docker compose exec -T php php artisan migrate --path=database/migrations/0001_01_01_000000_create_users_table.php --force
table_exists personal_access_tokens || docker compose exec -T php php artisan migrate --path="$TOKENS_MIGRATION" --force
docker compose exec -T php php artisan db:seed --class=StudentSeeder --force
docker compose exec -T php php artisan db:seed --class=UserSeeder --force
docker compose exec -T php php artisan route:list --path=api

# Quick check: the public route works through nginx -> php -> mysql
if command -v curl >/dev/null 2>&1; then
    curl -fsS -H 'Accept: application/json' http://localhost:8000/api/students/1 >/dev/null \
        && echo "Public route OK: http://localhost:8000/api/students/1"
fi

docker compose ps
echo "Setup complete. Database: studentdb (in the mysql container)"
echo "Website and API on port 8000 (nginx):  http://localhost:8000"
echo "To test: bash \"$SCRIPT_DIR/test1-5-docker.sh\""
echo "         or open \"$SCRIPT_DIR/run1-5-tests/index.html\" in your browser"
echo "To stop: docker compose down"
