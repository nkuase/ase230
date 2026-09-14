#!/bin/bash
cd "$(dirname "$0")"

source "$(dirname "$0")/ensure_install_packages.sh"

STOP_PHP=true
STOP_MYSQL=true

[ "${1:-}" = "--php" ]   && STOP_MYSQL=false
[ "${1:-}" = "--mysql" ] && STOP_PHP=false

if [ "$STOP_PHP" = true ]; then
    stop_php 8080
fi

if [ "$STOP_MYSQL" = true ]; then
    stop_mysql
fi

echo "Done."
