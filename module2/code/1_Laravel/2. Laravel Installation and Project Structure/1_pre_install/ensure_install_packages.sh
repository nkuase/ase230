#!/usr/bin/env bash
if [ "${BASH_SOURCE[0]}" = "$0" ]; then
    set -euo pipefail
fi

# 1. Detect platform: macOS or Ubuntu (22.04/24.04 / WSL2)
detect_platform() {
    if [ "$(uname -s)" = "Darwin" ]; then
        echo "mac"
        return 0
    fi
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        if [ "${ID:-}" = "ubuntu" ]; then
            echo "ubuntu"
            return 0
        fi
    fi
    echo "ERROR: Unsupported OS. This script officially supports macOS and Ubuntu (22.04/24.04 / WSL2)." >&2
    return 1
}

PLATFORM="$(detect_platform)" || exit 1

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=../0_version_check/version.sh
source "$SCRIPT_DIR/../0_version_check/version.sh"  # provides MIN_PHP_VERSION, PHP_SERIES

# Course database configuration (fixed for course reliability and security)
readonly DB_DATABASE="studentdb"
readonly DB_USERNAME="ase230"
readonly DB_PASSWORD="ase230pass"
readonly COURSE_PHP_SERIES="$PHP_SERIES"  # PHP_SERIES: version.sh
readonly REQUIRED_PHP_EXTENSIONS="ctype curl dom fileinfo filter hash mbstring openssl pcre pdo session tokenizer xml pdo_mysql zip intl"

# Same names as Laravel's .env; child processes (php, mysql helpers) can read them.
export DB_DATABASE DB_USERNAME DB_PASSWORD

# Load Homebrew environment on macOS
if [ "$PLATFORM" = "mac" ]; then
    if [ -x /opt/homebrew/bin/brew ]; then
        eval "$(/opt/homebrew/bin/brew shellenv)"
    elif [ -x /usr/local/bin/brew ]; then
        eval "$(/usr/local/bin/brew shellenv)"
    fi
fi

run_sudo() {
    if [ "$(id -u)" -eq 0 ]; then
        "$@"
    else
        sudo "$@"
    fi
}

install_homebrew() {
    if ! command -v brew >/dev/null 2>&1; then
        echo "Homebrew not found. Installing Homebrew..."
        /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
        if [ -x /opt/homebrew/bin/brew ]; then
            eval "$(/opt/homebrew/bin/brew shellenv)"
        elif [ -x /usr/local/bin/brew ]; then
            eval "$(/usr/local/bin/brew shellenv)"
        fi
    fi
}

# brew/apt are already idempotent (re-running "install" on an existing,
# compatible package is a safe no-op), so these functions install
# unconditionally instead of first checking whether it is "necessary."

install_mac_packages() {
    install_homebrew

    echo "Installing PHP $COURSE_PHP_SERIES via Homebrew..."
    brew install "php@$COURSE_PHP_SERIES"
    brew link --overwrite --force "php@$COURSE_PHP_SERIES"

    echo "Installing MySQL via Homebrew..."
    brew install mysql
}

install_ubuntu_packages() {
    # Ubuntu 22.04 and 24.04 do not provide PHP 8.4 as their default `php`
    # package, so use one versioned package set on both releases.
    echo "Installing PHP $COURSE_PHP_SERIES, required extensions, and MySQL via APT..."
    run_sudo apt-get update
    run_sudo apt-get install -y ca-certificates software-properties-common
    run_sudo add-apt-repository -y ppa:ondrej/php
    run_sudo apt-get update
    run_sudo apt-get install -y \
        "php${COURSE_PHP_SERIES}-cli" \
        "php${COURSE_PHP_SERIES}-mysql" \
        "php${COURSE_PHP_SERIES}-curl" \
        "php${COURSE_PHP_SERIES}-mbstring" \
        "php${COURSE_PHP_SERIES}-xml" \
        "php${COURSE_PHP_SERIES}-zip" \
        "php${COURSE_PHP_SERIES}-intl" \
        mysql-server \
        default-mysql-client
    run_sudo update-alternatives --set php "/usr/bin/php${COURSE_PHP_SERIES}"
}

is_mysql_running() {
    mysqladmin ping >/dev/null 2>&1
}

start_mysql() {
    if is_mysql_running; then
        return 0
    fi
    echo "Starting MySQL service..."
    if [ "$PLATFORM" = "mac" ]; then
        if ! brew services start mysql; then
            echo "ERROR: Failed to start MySQL service via Homebrew." >&2
            return 1
        fi
    else
        if ! run_sudo service mysql start 2>/dev/null && ! run_sudo systemctl start mysql 2>/dev/null; then
            echo "ERROR: Failed to start MySQL service via 'service' or 'systemctl'." >&2
            return 1
        fi
    fi

    local retries=30
    while ! is_mysql_running; do
        sleep 1
        retries=$((retries - 1))
        if [ "$retries" -le 0 ]; then
            echo "ERROR: MySQL failed to respond within 30 seconds." >&2
            return 1
        fi
    done
    echo "MySQL is running and ready."
}

stop_mysql() {
    echo "Stopping MySQL service..."
    if [ "$PLATFORM" = "mac" ]; then
        brew services stop mysql 2>/dev/null || true
    else
        run_sudo service mysql stop >/dev/null 2>&1 || run_sudo systemctl stop mysql >/dev/null 2>&1 || true
    fi
}

stop_php() {
    local port="${1:-8080}"
    echo "Stopping PHP server on port $port..."
    pkill -f "php -S.*$port" 2>/dev/null || true
    if command -v lsof >/dev/null 2>&1; then
        local pids
        pids=$(lsof -t -i:"$port" 2>/dev/null || true)
        if [ -n "$pids" ]; then
            kill "$pids" 2>/dev/null || true
        fi
    fi
}

configure_course_database() {
    # Check if dedicated user can already authenticate and query
    if MYSQL_PWD="$DB_PASSWORD" mysql -u "$DB_USERNAME" "$DB_DATABASE" -e "SELECT 1;" >/dev/null 2>&1; then
        return 0
    fi

    local sql="CREATE DATABASE IF NOT EXISTS \`${DB_DATABASE}\`; \
CREATE USER IF NOT EXISTS '${DB_USERNAME}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}'; \
ALTER USER '${DB_USERNAME}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}'; \
GRANT ALL PRIVILEGES ON \`${DB_DATABASE}\`.* TO '${DB_USERNAME}'@'localhost'; \
FLUSH PRIVILEGES;"

    if [ "$PLATFORM" = "ubuntu" ]; then
        if ! run_sudo mysql -e "$sql" 2>/dev/null && ! mysql -u root -e "$sql" 2>/dev/null; then
            echo "ERROR: Failed to configure course database '${DB_DATABASE}' and user '${DB_USERNAME}'." >&2
            return 1
        fi
    else
        # macOS: Try passwordless root first
        if ! mysql -u root -e "$sql" 2>/dev/null; then
            if [ -t 0 ]; then
                echo "MySQL root access requires authentication."
                read -s -r -p "Enter MySQL root password: " root_pass
                echo ""
                if ! MYSQL_PWD="$root_pass" mysql -u root -e "$sql" 2>/dev/null; then
                    echo "ERROR: Failed to authenticate with MySQL root. Cannot configure '${DB_USERNAME}'." >&2
                    return 1
                fi
            else
                echo "ERROR: Cannot access MySQL root without password in non-interactive environment." >&2
                return 1
            fi
        fi
    fi

    # Verify configuration succeeded
    if ! MYSQL_PWD="$DB_PASSWORD" mysql -u "$DB_USERNAME" "$DB_DATABASE" -e "SELECT 1;" >/dev/null 2>&1; then
        echo "ERROR: Database '${DB_DATABASE}' and user '${DB_USERNAME}' setup could not be verified." >&2
        return 1
    fi
}

verify_installation() {
    if ! command -v php >/dev/null 2>&1 || ! php -v >/dev/null 2>&1; then
        echo "ERROR: PHP CLI is not installed or not working." >&2
        return 1
    fi

    local php_version
    php_version="$(php -r 'echo PHP_VERSION;')"
    if ! php -r 'exit(version_compare(PHP_VERSION, $argv[1], ">=") ? 0 : 1);' -- "$MIN_PHP_VERSION"; then
        echo "ERROR: PHP $MIN_PHP_VERSION or newer is required; found PHP $php_version." >&2
        return 1
    fi

    local extension
    for extension in $REQUIRED_PHP_EXTENSIONS; do
        if ! php -r 'exit(extension_loaded($argv[1]) ? 0 : 1);' -- "$extension"; then
            echo "ERROR: Required PHP extension '$extension' is not loaded." >&2
            return 1
        fi
    done

    if ! is_mysql_running; then
        echo "ERROR: MySQL server is not running." >&2
        return 1
    fi

    if ! MYSQL_PWD="$DB_PASSWORD" mysql -u "$DB_USERNAME" "$DB_DATABASE" -e "SELECT 1;" >/dev/null 2>&1; then
        echo "ERROR: Cannot query '${DB_DATABASE}' database as '${DB_USERNAME}'." >&2
        return 1
    fi

    echo "✅ All components verified successfully (PHP $php_version, extensions, MySQL & studentdb)."
    return 0
}

main() {
    echo "=== Student API Environment Setup ($PLATFORM) ==="
    if [ "$PLATFORM" = "mac" ]; then
        install_mac_packages
    else
        install_ubuntu_packages
    fi
    start_mysql
    configure_course_database
    verify_installation
}

if [ "${BASH_SOURCE[0]}" = "$0" ]; then
    main "$@"
fi
