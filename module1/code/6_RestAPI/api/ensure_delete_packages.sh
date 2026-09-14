#!/usr/bin/env bash
set -euo pipefail

detect_platform() {
    if [ "$(uname -s)" = "Darwin" ]; then
        echo "mac"
        return 0
    fi

    if [ -f /etc/os-release ]; then
        # shellcheck disable=SC1091
        . /etc/os-release
        if [ "${ID:-}" = "ubuntu" ]; then
            echo "ubuntu"
            return 0
        fi
    fi

    echo "ERROR: Unsupported OS. This script supports macOS and Ubuntu only." >&2
    return 1
}

run_sudo() {
    if [ "$(id -u)" -eq 0 ]; then
        "$@"
    else
        sudo "$@"
    fi
}

load_homebrew() {
    if [ -x /opt/homebrew/bin/brew ]; then
        eval "$(/opt/homebrew/bin/brew shellenv)"
    elif [ -x /usr/local/bin/brew ]; then
        eval "$(/usr/local/bin/brew shellenv)"
    fi
}

confirm_deletion() {
    if [ "${1:-}" = "--yes" ] || [ "${1:-}" = "-y" ]; then
        return 0
    fi

    if [ -n "${1:-}" ]; then
        echo "Usage: $0 [--yes]" >&2
        return 1
    fi

    if [ ! -t 0 ]; then
        echo "ERROR: Confirmation requires a terminal. Use --yes for unattended execution." >&2
        return 1
    fi

    echo "WARNING: This permanently removes PHP, MySQL, all local MySQL databases, and their configuration."
    echo "Other software that depends on PHP or MySQL may stop working."
    read -r -p "Type DELETE to continue: " answer
    if [ "$answer" != "DELETE" ]; then
        echo "Cancelled. Nothing was removed."
        exit 0
    fi
}

remove_mac_packages() {
    load_homebrew
    if ! command -v brew >/dev/null 2>&1; then
        echo "Homebrew is not installed; there are no course packages to remove."
        return 0
    fi

    local brew_prefix
    brew_prefix="$(brew --prefix)"
    if [[ "$brew_prefix" != /* ]] || [ "$brew_prefix" = "/" ]; then
        echo "ERROR: Refusing unsafe Homebrew prefix: '$brew_prefix'." >&2
        return 1
    fi

    local formulae=()
    local formula
    while IFS= read -r formula; do
        case "$formula" in
            php|php@*|mysql|mysql@*|mysql-client|mysql-client@*)
                formulae+=("$formula")
                ;;
        esac
    done < <(brew list --formula)

    if [ ${#formulae[@]} -gt 0 ]; then
        echo "Removing Homebrew formulae: ${formulae[*]}"
        for formula in "${formulae[@]}"; do
            brew services stop "$formula" >/dev/null 2>&1 || true
        done
        brew uninstall --force --ignore-dependencies "${formulae[@]}"
    else
        echo "No Homebrew PHP or MySQL formulae were found."
    fi

    rm -rf -- "$brew_prefix/var/mysql"
    rm -rf -- "$brew_prefix/etc/my.cnf.d"
    rm -rf -- "$brew_prefix/etc/php"
    rm -f -- "$brew_prefix/etc/my.cnf"

}

installed_ubuntu_packages() {
    local package status
    while read -r package status; do
        [ "$status" = "ii" ] || continue
        case "$package" in
            php|php-*|php[0-9]*|mysql-*|default-mysql-*)
                printf '%s\n' "$package"
                ;;
        esac
    done < <(dpkg-query -W -f='${binary:Package} ${db:Status-Abbrev}\n' 2>/dev/null || true)
}

remove_ubuntu_packages() {
    run_sudo service mysql stop >/dev/null 2>&1 || run_sudo systemctl stop mysql >/dev/null 2>&1 || true

    local packages=()
    local package
    while IFS= read -r package; do
        [ -n "$package" ] && packages+=("$package")
    done < <(installed_ubuntu_packages)

    if [ ${#packages[@]} -gt 0 ]; then
        echo "Purging Ubuntu packages: ${packages[*]}"
        run_sudo apt-get purge -y "${packages[@]}"
    else
        echo "No installed PHP or MySQL packages were found."
    fi

    run_sudo rm -rf -- /var/lib/mysql
    run_sudo rm -rf -- /var/lib/mysql-files
    run_sudo rm -rf -- /var/lib/mysql-keyring
    run_sudo rm -rf -- /var/log/mysql
    run_sudo rm -rf -- /run/mysqld
    run_sudo rm -rf -- /etc/mysql
    run_sudo rm -rf -- /etc/php

    if [ -n "$(installed_ubuntu_packages)" ]; then
        echo "ERROR: Some PHP or MySQL packages are still installed." >&2
        return 1
    fi
}

verify_deletion() {
    hash -r
    local remaining=()
    local command_name
    for command_name in php mysql mysqladmin mysqld; do
        if command -v "$command_name" >/dev/null 2>&1; then
            remaining+=("$command_name")
        fi
    done

    if [ ${#remaining[@]} -gt 0 ]; then
        echo "ERROR: Commands still found after deletion: ${remaining[*]}" >&2
        echo "They may have been installed outside Homebrew/APT." >&2
        return 1
    fi

    echo "PHP and MySQL packages, databases, and configuration were removed successfully."
    echo "Run ./ensure_install_packages.sh to perform a clean installation."
}

main() {
    local platform
    platform="$(detect_platform)"

    echo "=== Student API Environment Removal ($platform) ==="
    confirm_deletion "${1:-}"

    if [ "$platform" = "mac" ]; then
        remove_mac_packages
    else
        remove_ubuntu_packages
    fi

    verify_deletion
}

main "$@"
