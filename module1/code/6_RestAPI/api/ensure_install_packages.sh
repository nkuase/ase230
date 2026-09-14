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

# Course database configuration (fixed for course reliability and security)
readonly COURSE_DB="studentdb"
readonly COURSE_USER="ase230"
readonly COURSE_PASS="ase230pass"

export DB_NAME="$COURSE_DB"
export DB_USER="$COURSE_USER"
export DB_PASSWORD="$COURSE_PASS"

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

install_mac_packages() {
    install_homebrew
    if ! brew list --versions php >/dev/null 2>&1; then
        echo "Installing PHP via Homebrew..."
        brew install php
    fi
    if ! brew list --versions mysql >/dev/null 2>&1; then
        echo "Installing MySQL via Homebrew..."
        brew install mysql
    fi
}

install_ubuntu_packages() {
    if ! command -v php >/dev/null 2>&1 || ! command -v mysql >/dev/null 2>&1 || \
       ! dpkg -s php-mysql >/dev/null 2>&1 || ! dpkg -s mysql-server >/dev/null 2>&1; then
        echo "Installing packages via APT (php-cli, php-mysql, mysql-server, default-mysql-client)..."
        run_sudo apt-get update
        run_sudo apt-get install -y php-cli php-mysql mysql-server default-mysql-client
    fi
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
    if MYSQL_PWD="$COURSE_PASS" mysql -u "$COURSE_USER" "$COURSE_DB" -e "SELECT 1;" >/dev/null 2>&1; then
        return 0
    fi

    local sql="CREATE DATABASE IF NOT EXISTS \`${COURSE_DB}\`; \
CREATE USER IF NOT EXISTS '${COURSE_USER}'@'localhost' IDENTIFIED BY '${COURSE_PASS}'; \
ALTER USER '${COURSE_USER}'@'localhost' IDENTIFIED BY '${COURSE_PASS}'; \
GRANT ALL PRIVILEGES ON \`${COURSE_DB}\`.* TO '${COURSE_USER}'@'localhost'; \
FLUSH PRIVILEGES;"

    if [ "$PLATFORM" = "ubuntu" ]; then
        if ! run_sudo mysql -e "$sql" 2>/dev/null && ! mysql -u root -e "$sql" 2>/dev/null; then
            echo "ERROR: Failed to configure course database '${COURSE_DB}' and user '${COURSE_USER}'." >&2
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
                    echo "ERROR: Failed to authenticate with MySQL root. Cannot configure '${COURSE_USER}'." >&2
                    return 1
                fi
            else
                echo "ERROR: Cannot access MySQL root without password in non-interactive environment." >&2
                return 1
            fi
        fi
    fi

    # Verify configuration succeeded
    if ! MYSQL_PWD="$COURSE_PASS" mysql -u "$COURSE_USER" "$COURSE_DB" -e "SELECT 1;" >/dev/null 2>&1; then
        echo "ERROR: Database '${COURSE_DB}' and user '${COURSE_USER}' setup could not be verified." >&2
        return 1
    fi
}

verify_installation() {
    if ! command -v php >/dev/null 2>&1 || ! php -v >/dev/null 2>&1; then
        echo "ERROR: PHP CLI is not installed or not working." >&2
        return 1
    fi

    if ! php -r "exit(class_exists('PDO') ? 0 : 1);"; then
        echo "ERROR: PHP PDO extension is missing." >&2
        return 1
    fi

    if ! php -r "exit(extension_loaded('pdo_mysql') ? 0 : 1);"; then
        echo "ERROR: PHP pdo_mysql extension is not loaded." >&2
        return 1
    fi

    if ! is_mysql_running; then
        echo "ERROR: MySQL server is not running." >&2
        return 1
    fi

    if ! MYSQL_PWD="$COURSE_PASS" mysql -u "$COURSE_USER" "$COURSE_DB" -e "SELECT 1;" >/dev/null 2>&1; then
        echo "ERROR: Cannot query '${COURSE_DB}' database as '${COURSE_USER}'." >&2
        return 1
    fi

    echo "✅ All components verified successfully (PHP, PDO, pdo_mysql, MySQL & studentdb)."
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
