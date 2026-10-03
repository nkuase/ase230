#!/usr/bin/env bash
# Shared by mac_install.sh and wsl2_install.sh.
# Source this after sourcing ../0_version_check/version.sh:
#   source "./common_install.sh"
#
# Requires MIN_PHP_VERSION and PHP_SERIES to already be set
# (they come from ../0_version_check/version.sh).

# PRE-install gate: checks PHP only, and must run BEFORE Composer is
# installed. This is not redundant with version_check() below - the two
# run at different times and stop different problems:
#   - On macOS, "brew install composer" depends on the "php" formula, so if
#     system PHP is missing/too old, Homebrew will silently install its OWN
#     (wrong) PHP as a side effect. This gate stops that before it happens.
#   - On WSL2, installing Composer runs `php -r ...` directly, so without
#     this gate a missing PHP would fail with a raw "command not found"
#     instead of a clear, actionable message.
# By the time version_check() runs (after install), it would already be
# too late to prevent either of those problems.
require_min_php() {
    if ! command -v php >/dev/null 2>&1 || \
       ! php -r 'exit(version_compare(PHP_VERSION, $argv[1], ">=") ? 0 : 1);' -- "$MIN_PHP_VERSION"; then  # MIN_PHP_VERSION: version.sh
        echo "ERROR: PHP $MIN_PHP_VERSION or newer is required." >&2
        echo "Run ../1_pre_install/ensure_install_packages.sh first." >&2
        exit 1
    fi
}

# POST-install check: runs AFTER Composer is installed, as the final
# "did Step 1 + Step 2 actually succeed" confirmation. It re-checks PHP
# (require_min_php already gated that earlier, so this should always pass
# here) plus the things require_min_php does NOT check - required PHP
# extensions and the installed Composer version. See
# ../0_version_check/version_check.sh for what each check does.
version_check() {
    echo "=== Checking the course requirements ==="
    bash ../0_version_check/version_check.sh
}

print_step2_complete() {
    echo ""
    echo "Step 2 complete: PHP $PHP_SERIES+ and Composer are ready."  # PHP_SERIES: version.sh
    echo ""
    echo "Next step - create your Laravel project:"
    echo "  cd .."
    echo "  bash ./run1-2.sh"
}
