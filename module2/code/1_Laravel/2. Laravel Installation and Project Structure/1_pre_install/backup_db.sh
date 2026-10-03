#!/usr/bin/env bash
# Back up one MySQL database to DATABASE_YYYYMMDD_HHMMSS.sql
# Set MYSQL_BACKUP_DIR to choose the folder (default: current folder).
set -euo pipefail
source "$(dirname "$0")/common.sh"

[ "$#" -eq 1 ] || fail "Usage: $0 DATABASE"
DATABASE="$1"
[[ "$DATABASE" =~ ^[A-Za-z0-9_\$]+$ ]] || fail "Invalid database name '$DATABASE' (use letters, numbers, _ and \$)."
command -v mysqldump >/dev/null || fail "mysqldump is not in PATH."

BACKUP_DIR="${MYSQL_BACKUP_DIR:-$PWD}"
BACKUP_FILE="$BACKUP_DIR/${DATABASE}_$(date +%Y%m%d_%H%M%S).sql"

# Work in a hidden temp folder, so a failed backup leaves nothing behind.
umask 077
mkdir -p "$BACKUP_DIR"
WORK_DIR="$(mktemp -d "$BACKUP_DIR/.work.XXXXXX")"
trap 'rm -rf "$WORK_DIR"' EXIT
DUMP_FILE="$WORK_DIR/dump.sql"
LOG_FILE="$WORK_DIR/error.log"
touch "$LOG_FILE"

DUMP_OPTIONS=(--databases "$DATABASE" --triggers --single-transaction --quick
              --set-gtid-purged=OFF --no-tablespaces "--log-error=$LOG_FILE")

# Newer mysqldump also dumps server-wide masking policies; a course user should not.
mysqldump_has --masking-policies && DUMP_OPTIONS+=(--skip-masking-policies)

# Keep the backup only if it is complete (some versions print errors but exit 0).
if ! run_mysql mysqldump "${DUMP_OPTIONS[@]}" > "$DUMP_FILE" \
   || grep -Eiq 'error:|couldn.t execute|access denied' "$LOG_FILE" \
   || ! grep -Fq "USE \`$DATABASE\`;" "$DUMP_FILE"; then
    cat "$LOG_FILE" >&2
    fail "Backup failed or is incomplete. Nothing was saved."
fi

mv "$DUMP_FILE" "$BACKUP_FILE"
echo "✅ Backup completed: $BACKUP_FILE"
echo "Restore with: bash \"$(dirname "$0")/restore_db.sh\" \"$BACKUP_FILE\""
