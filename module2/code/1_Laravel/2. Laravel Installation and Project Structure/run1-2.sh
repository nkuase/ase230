#!/bin/bash

# Simple Laravel Student API Setup Script
TARGET_DIR="student-api"

set -e

# If a previous project already exists, back it up with a timestamp instead of
# letting `composer create-project` fail on a non-empty directory.
if [ -d "$TARGET_DIR" ]; then
    BACKUP_DIR="${TARGET_DIR}_backup_$(date +%Y%m%d_%H%M%S)"
    echo "Existing '$TARGET_DIR' found. Backing it up to '$BACKUP_DIR'..."
    mv "$TARGET_DIR" "$BACKUP_DIR"
fi

echo "Creating Laravel project..."
# The course environment requires PHP 8.4+, so Composer can install the current
# Laravel release without pinning the project to Laravel 12.
composer create-project laravel/laravel "$TARGET_DIR"

# Fail clearly if a future dependency change creates an unexpected project.
bash "$(dirname "$0")/0_version_check/version_check.sh" "$TARGET_DIR"

echo ""
echo "✅ Setup complete!"
echo ""
echo ""
echo "To start server on port 8080:"
echo "  cd $TARGET_DIR"
echo "  php artisan serve --port=8080"
echo "  WSL2: php artisan serve --host=0.0.0.0 --port=8080"
