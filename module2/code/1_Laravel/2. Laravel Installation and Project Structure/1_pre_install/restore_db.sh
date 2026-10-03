#!/usr/bin/env bash
# Restore a database from a backup file made by backup_db.sh.
#   restore_db.sh BACKUP_FILE.sql
set -euo pipefail
source "$(dirname "$0")/common.sh"

[ "$#" -eq 1 ] || fail "Usage: $0 BACKUP_FILE.sql"
BACKUP_FILE="$1"

command -v mysql >/dev/null || fail "mysql is not in PATH."
[ -s "$BACKUP_FILE" ] || fail "Backup file is missing or empty: $BACKUP_FILE"

echo "Restoring from '$BACKUP_FILE'..."
run_mysql mysql < "$BACKUP_FILE"
echo "✅ Restore completed."
