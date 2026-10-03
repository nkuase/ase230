#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=./version.sh
source "$SCRIPT_DIR/version.sh"  # provides MIN_PHP_VERSION, MIN_COMPOSER_VERSION, MIN_LARAVEL_VERSION

readonly REQUIRED_PHP_EXTENSIONS="ctype curl dom fileinfo filter hash mbstring openssl pcre pdo session tokenizer xml pdo_mysql zip intl"

PHP_AVAILABLE=0

detect_platform() {
    if [ "$(uname -s)" = "Darwin" ]; then
        echo "mac"
        return 0
    fi

    if [ -f /etc/os-release ]; then
        # shellcheck disable=SC1091
        . /etc/os-release
        if [ "${ID:-}" = "ubuntu" ]; then
            echo "ubuntu"
            return 0
        fi
    fi

    # Version checks are portable, so an unknown OS is a warning, not a failure.
    echo "unknown"
}

version_is_at_least() {
    local actual_version="$1"
    local minimum_version="$2"

    php -r 'exit(version_compare($argv[1], $argv[2], ">=") ? 0 : 1);' -- \
        "$actual_version" "$minimum_version"
}

print_php_install_hint() {
    local platform="$1"

    case "$platform" in
        mac)
            echo "Hint: install PHP 8.4 with Homebrew: brew install php@8.4" >&2
            ;;
        ubuntu)
            echo "Hint: install the php8.4-cli package and required PHP 8.4 extensions." >&2
            ;;
        *)
            echo "Hint: install PHP $MIN_PHP_VERSION or newer for the course environment." >&2
            ;;
    esac
    echo "Hint: run 1_pre_install/ensure_install_packages.sh before continuing." >&2
}

check_php() {
    local platform="$1"

    if ! command -v php >/dev/null 2>&1; then
        echo "ERROR: PHP is not installed or is not available in PATH." >&2
        print_php_install_hint "$platform"
        return 1
    fi

    local php_version
    php_version="$(php -r 'echo PHP_VERSION;')"

    if ! version_is_at_least "$php_version" "$MIN_PHP_VERSION"; then  # MIN_PHP_VERSION: version.sh
        echo "ERROR: PHP $MIN_PHP_VERSION or newer is required; found PHP $php_version." >&2
        echo "PHP executable: $(command -v php)" >&2
        print_php_install_hint "$platform"
        return 1
    fi

    PHP_AVAILABLE=1
    echo "✅ PHP version OK: $php_version ($(command -v php))"
}

check_php_extensions() {
    if [ "$PHP_AVAILABLE" -ne 1 ]; then
        echo "ℹ️ PHP extension check skipped because PHP is unavailable or too old."
        return 0
    fi

    local extension
    local missing=""

    # Laravel needs these extensions before Composer can install and run it.
    for extension in $REQUIRED_PHP_EXTENSIONS; do
        if ! php -r 'exit(extension_loaded($argv[1]) ? 0 : 1);' -- "$extension"; then
            missing="$missing $extension"
        fi
    done

    if [ -n "$missing" ]; then
        echo "ERROR: Missing required PHP extensions:$missing" >&2
        return 1
    fi

    echo "✅ Required PHP extensions OK"
}

check_composer() {
    if ! command -v composer >/dev/null 2>&1; then
        echo "ERROR: Composer is not installed or is not available in PATH." >&2
        return 1
    fi

    local composer_output
    local composer_version
    if ! composer_output="$(composer --version 2>&1)"; then
        echo "ERROR: Composer is installed but could not run." >&2
        echo "$composer_output" >&2
        return 1
    fi

    composer_version="$(printf '%s\n' "$composer_output" | sed -nE 's/.*Composer version ([0-9]+(\.[0-9]+){1,2}).*/\1/p' | head -n 1)"
    if [ -z "$composer_version" ]; then
        echo "ERROR: Unexpected Composer version output: $composer_output" >&2
        return 1
    fi

    if ! version_is_at_least "$composer_version" "$MIN_COMPOSER_VERSION"; then  # MIN_COMPOSER_VERSION: version.sh
        echo "ERROR: Composer $MIN_COMPOSER_VERSION or newer is required; found Composer $composer_version." >&2
        return 1
    fi

    echo "✅ Composer version OK: $composer_version"
}

check_laravel() {
    if [ "$PHP_AVAILABLE" -ne 1 ]; then
        echo "ℹ️ Laravel version check skipped because PHP is unavailable or too old."
        return 0
    fi

    if [ ! -f "$1/artisan" ]; then
        echo "ERROR: Laravel artisan file not found in '$1'." >&2
        return 1
    fi

    local project_dir
    project_dir="$(cd "$1" && pwd)"

    local artisan_output
    local laravel_version

    if ! artisan_output="$(cd "$project_dir" && php artisan --version 2>&1)"; then
        echo "ERROR: Could not read the Laravel version in '$project_dir'." >&2
        echo "$artisan_output" >&2
        return 1
    fi

    laravel_version="$(printf '%s\n' "$artisan_output" | sed -nE 's/^Laravel Framework ([0-9]+(\.[0-9]+){1,2}).*/\1/p')"
    if [ -z "$laravel_version" ]; then
        echo "ERROR: Unexpected Laravel version output: $artisan_output" >&2
        return 1
    fi

    if ! version_is_at_least "$laravel_version" "$MIN_LARAVEL_VERSION"; then  # MIN_LARAVEL_VERSION: version.sh
        echo "ERROR: Laravel $MIN_LARAVEL_VERSION or newer is required; found Laravel $laravel_version." >&2
        echo "Laravel project: $project_dir" >&2
        return 1
    fi

    echo "✅ Laravel version OK: $laravel_version ($project_dir)"
}

main() {
    local platform
    local failures=0

    if [ "$#" -gt 1 ]; then
        echo "Usage: $0 [LARAVEL_PROJECT_DIR]" >&2
        return 1
    fi

    platform="$(detect_platform)"

    echo "=== Version Check ($platform) ==="
    if [ "$platform" = "unknown" ]; then
        echo "WARNING: This course officially supports macOS and Ubuntu; continuing with portable checks." >&2
    fi

    check_php "$platform" || failures=$((failures + 1))
    check_php_extensions || failures=$((failures + 1))
    check_composer || failures=$((failures + 1))
    if [ "$#" -eq 0 ]; then
        echo "ℹ️ Laravel version check skipped; no project directory was provided."
    else
        check_laravel "$1" || failures=$((failures + 1))
    fi

    if [ "$failures" -gt 0 ]; then
        echo "❌ $failures check(s) failed." >&2
        return 1
    fi

    echo "✅ All available checks passed."
}

main "$@"
