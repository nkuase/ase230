#!/usr/bin/env bash
# Single source of truth for the course's required tool versions.
# Other scripts source this file instead of repeating these numbers:
#   source "$(dirname "${BASH_SOURCE[0]}")/version.sh"

readonly MIN_PHP_VERSION="8.4.0"
readonly MIN_COMPOSER_VERSION="2.8.0"
readonly MIN_LARAVEL_VERSION="13.0.0"

# Derived from MIN_PHP_VERSION ("8.4.0" -> "8.4"), e.g. for the "php@8.4"
# package name used by Homebrew/APT.
readonly PHP_SERIES="${MIN_PHP_VERSION%.*}"

# Exports (used by version_check.sh, ensure_install_packages.sh,
# mac_install.sh, wsl2_install.sh, common_install.sh):
#   MIN_PHP_VERSION, MIN_COMPOSER_VERSION, MIN_LARAVEL_VERSION, PHP_SERIES
