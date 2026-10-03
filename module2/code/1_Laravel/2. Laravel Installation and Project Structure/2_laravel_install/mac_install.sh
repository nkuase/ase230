#!/usr/bin/env bash
# Step 2 (macOS): Install Composer, then confirm PHP 8.4+ and Composer are ready.
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

if [ -x /opt/homebrew/bin/brew ]; then
    eval "$(/opt/homebrew/bin/brew shellenv)"
elif [ -x /usr/local/bin/brew ]; then
    eval "$(/usr/local/bin/brew shellenv)"
fi

if ! command -v brew >/dev/null 2>&1; then
    echo "ERROR: Homebrew is not installed or is not available in PATH." >&2
    echo "Run 1_pre_install/ensure_install_packages.sh first." >&2
    exit 1
fi

# Step 1 owns PHP and extension installation. Stop here with a focused message
# instead of letting Homebrew silently pull in an unrelated PHP version as a
# dependency of the Composer formula.
require_min_php       # common_install.sh (pre-install gate: PHP only)

echo "=== Installing Composer ==="
brew install composer
hash -r

version_check         # common_install.sh (post-install check: PHP + extensions + Composer)
print_step2_complete  # common_install.sh
