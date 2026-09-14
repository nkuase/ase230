#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")"

source "$(dirname "$0")/ensure_install_packages.sh"

echo "Resetting JSON storage (data/students.json)..."
mkdir -p data
echo '[]' > data/students.json

if [ "${1:-}" = "--mysql" ] || [ "${1:-}" = "--pdo" ]; then
    start_mysql
    configure_course_database

    export MYSQL_PWD="$COURSE_PASS"

    echo "Resetting MySQL database ($COURSE_DB)..."
    mysql -u "$COURSE_USER" -e "DROP DATABASE IF EXISTS \`$COURSE_DB\`; CREATE DATABASE \`$COURSE_DB\`;" || {
        echo "ERROR: Failed to recreate database '$COURSE_DB'." >&2
        exit 1
    }
    mysql -u "$COURSE_USER" "$COURSE_DB" < schema.sql || {
        echo "ERROR: Failed to import schema.sql into '$COURSE_DB'." >&2
        exit 1
    }
fi

echo "Done."
