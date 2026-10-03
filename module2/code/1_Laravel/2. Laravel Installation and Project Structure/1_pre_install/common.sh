# common.sh - helpers shared by backup_db.sh and restore_db.sh
#
# This file is "sourced", not executed. Each script loads it with:
#     source "$(dirname "$0")/common.sh"
# so the functions below become available inside that script.
#
# Optional environment variables (set them before running a script):
#   MYSQL_USER  login user (default: ase230)
#   MYSQL_HOST  server host (default: localhost)
#   MYSQL_PWD   password; if unset, MySQL asks for it
#
# Example:  MYSQL_USER=root bash backup_db.sh studentdb


# fail MESSAGE
# Print an error message to stderr and stop the script.
#     command -v mysql >/dev/null || fail "mysql is not in PATH."
fail() {
    echo "ERROR: $*" >&2
    exit 1
}

# run_mysql CLIENT [ARGS...]
# Run a MySQL client (mysql or mysqldump) with the course login options,
# so the scripts do not repeat -u/-h/-p everywhere.
#     run_mysql mysql < backup.sql
#     run_mysql mysqldump --databases mydb > backup.sql
run_mysql() {
    local command="$1"
    shift    # the remaining arguments stay in "$@"

    # ${VAR:-default} means "use VAR, or default if VAR is not set".
    local login=(-u "${MYSQL_USER:-ase230}" -h "${MYSQL_HOST:-localhost}")

    # -p makes MySQL ask for the password, unless MYSQL_PWD already has one.
    [ -n "${MYSQL_PWD:-}" ] || login+=(-p)

    "$command" "${login[@]}" "$@"
}

# mysqldump_has OPTION
# True if the installed mysqldump knows OPTION. Versions differ: for example,
# Homebrew's mysqldump has --skip-masking-policies, Ubuntu's 8.0 does not.
#     mysqldump_has --masking-policies && DUMP_OPTIONS+=(--skip-masking-policies)
mysqldump_has() {
    local help
    help="$(mysqldump --help 2>&1 || true)"    # "|| true": do not stop if --help exits non-zero
    [[ "$help" == *"$1"* ]]                    # does the help text contain OPTION?
}
