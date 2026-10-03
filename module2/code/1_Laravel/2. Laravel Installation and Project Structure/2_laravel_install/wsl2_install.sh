#!/usr/bin/env bash
# Step 2 (WSL2/Ubuntu): Install Composer and configure WSL2 networking.
# Run 1_pre_install/ensure_install_packages.sh first.
#
# This script does NOT touch MySQL and does NOT create a Laravel project.
# studentdb/ase230 already exist from Step 1.
set -euo pipefail
cd "$(dirname "$0")"

# shellcheck source=../0_version_check/version.sh
source "../0_version_check/version.sh"    # provides MIN_PHP_VERSION, PHP_SERIES
# shellcheck source=./common_install.sh
source "./common_install.sh"              # provides require_min_php, version_check, print_step2_complete

# Step 1 owns PHP and extension installation. Stop here with a focused message
# instead of installing a second, conflicting PHP version in Step 2.
require_min_php  # common_install.sh (pre-install gate: PHP only)

install_current_composer() {
    local expected_checksum
    local actual_checksum
    local installer

    installer="$(mktemp)"
    expected_checksum="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
    php -r 'copy("https://getcomposer.org/installer", $argv[1]);' -- "$installer"
    actual_checksum="$(php -r 'echo hash_file("sha384", $argv[1]);' -- "$installer")"

    if [ "$expected_checksum" != "$actual_checksum" ]; then
        rm -f "$installer"
        echo "ERROR: Composer installer checksum verification failed." >&2
        return 1
    fi

    sudo php "$installer" --quiet --2 --install-dir=/usr/local/bin --filename=composer
    rm -f "$installer"
    hash -r
}

echo "=== Installing the current Composer 2 release ==="
install_current_composer

version_check    # common_install.sh (post-install check: PHP + extensions + Composer)

echo "=== Configuring WSL2 networking (make_wslconfig.sh) ==="
# Lets http://localhost:8080 be reachable from a Windows browser. A "File
# already exists" message here is fine - it means a previous run already set
# this up, so it is not treated as a failure.
bash make_wslconfig.sh || true

print_step2_complete  # common_install.sh
echo ""
echo "If make_wslconfig.sh just created a new .wslconfig (see above), restart WSL2 once:"
echo "  In a Windows terminal (not WSL2): wsl --shutdown"
echo "  Then reopen your WSL2 terminal."
