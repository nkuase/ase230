#!/bin/bash
set -e

SOURCE_DIR="orm-reference"
TARGET_DIR="orm-practice"
MYSQL_PASSWORD="your_local_root_password"
DB_DATABASE="orm_practice"
DB_USERNAME="laravel_user"
DB_PASSWORD="your_local_password"

if [ -e "$TARGET_DIR" ]; then
    echo "Use a fresh practice directory; $TARGET_DIR already exists."
    exit 1
fi

# Create the Laravel app without running its default database setup.
composer create-project laravel/laravel "$TARGET_DIR" "^13.0" --no-scripts
cp -R "$SOURCE_DIR"/. "$TARGET_DIR"/
cd "$TARGET_DIR"

# Use the same local MySQL setup as the Laravel lessons.
if [[ "$OSTYPE" == "darwin"* ]]; then
    MYSQL="mysql"
else
    MYSQL="sudo mysql"
fi

# CREATE DATABASE stops if this name is already in use.
$MYSQL -u root -p"$MYSQL_PASSWORD" <<EOF
CREATE DATABASE $DB_DATABASE;
CREATE USER IF NOT EXISTS '$DB_USERNAME'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON $DB_DATABASE.* TO '$DB_USERNAME'@'localhost';
EOF

cat > .env <<EOF
APP_NAME=OrmPractice
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=$DB_DATABASE
DB_USERNAME=$DB_USERNAME
DB_PASSWORD="$DB_PASSWORD"
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
EOF

php artisan key:generate
php artisan package:discover
php artisan migrate --step
php artisan db:seed --class=OrmPracticeSeeder

echo "Ready: cd $TARGET_DIR, then run php artisan tinker."
