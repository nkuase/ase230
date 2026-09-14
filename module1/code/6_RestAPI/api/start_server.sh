#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")"

source "$(dirname "$0")/ensure_install_packages.sh"

STORAGE="json"
RESET=true

for arg in "$@"; do
    case "$arg" in
        --pdo|--mysql) STORAGE="pdo" ;;
        --no-reset)    RESET=false ;;
    esac
done

if [ "$PLATFORM" = "mac" ]; then
    install_mac_packages
else
    install_ubuntu_packages
fi

if [ "$STORAGE" = "pdo" ]; then
    start_mysql
    configure_course_database
    verify_installation
    if [ "$RESET" = true ]; then
        ./reset_db.sh --mysql || exit 1
    else
        export MYSQL_PWD="$COURSE_PASS"
        mysql -u "$COURSE_USER" "$COURSE_DB" < schema.sql || {
            echo "ERROR: Failed to ensure database schema for '$COURSE_DB'." >&2
            exit 1
        }
    fi
else
    command -v php >/dev/null 2>&1 || { echo "ERROR: PHP is required." >&2; exit 1; }
    if [ "$RESET" = true ]; then
        ./reset_db.sh || exit 1
    else
        mkdir -p data
        [ ! -f data/students.json ] && echo '[]' > data/students.json
    fi
fi

echo "=========================================="
echo "Starting Student API ($STORAGE mode)"
echo "URL:     http://localhost:8080/"
echo "Test UI: http://localhost:8080/student_api_test.html"
echo "         (Access via HTTP; NEVER open as file://)"
echo "=========================================="

STUDENT_STORAGE="$STORAGE" exec php -S localhost:8080
