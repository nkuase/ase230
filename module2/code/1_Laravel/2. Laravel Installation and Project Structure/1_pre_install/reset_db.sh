#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")"

# provides: start_mysql(), configure_course_database(), and
# DB_DATABASE / DB_USERNAME / DB_PASSWORD (exported by that script)
source "$(dirname "$0")/ensure_install_packages.sh"

case "${1:-}" in
    "")            USE_MYSQL=false ;;
    --mysql|--pdo) USE_MYSQL=true ;;
    *)             echo "Usage: $0 [--mysql|--pdo]" >&2; exit 1 ;;
esac
[ "$USE_MYSQL" = false ] || [ -r schema.sql ] || { echo "ERROR: schema.sql not found." >&2; exit 1; }

echo "Resetting JSON storage (data/students.json)..."
mkdir -p data
echo '[]' > data/students.json

if [ "$USE_MYSQL" = true ]; then
    start_mysql               # ensure_install_packages.sh
    configure_course_database # ensure_install_packages.sh

    export MYSQL_PWD="$DB_PASSWORD"

    echo "Resetting MySQL database ($DB_DATABASE)..."
    mysql -u "$DB_USERNAME" -e "DROP DATABASE IF EXISTS \`$DB_DATABASE\`; CREATE DATABASE \`$DB_DATABASE\`;" || {
        echo "ERROR: Failed to recreate database '$DB_DATABASE'." >&2
        exit 1
    }
    mysql -u "$DB_USERNAME" "$DB_DATABASE" < schema.sql || {
        echo "ERROR: Failed to import schema.sql into '$DB_DATABASE'." >&2
        exit 1
    }
fi

echo "Done."
