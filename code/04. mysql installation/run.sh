#!/usr/bin/env bash
set -euo pipefail

OS="linux"
if [ "$(uname -s)" = "Darwin" ]; then
    OS="mac"
elif grep -qi microsoft /proc/version 2>/dev/null; then
    OS="wsl2"
fi
echo "Detected platform: $OS"

if [ "$OS" = "mac" ]; then
    if ! command -v brew >/dev/null 2>&1; then
        echo "Installing Homebrew ..."
        /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
        # A fresh Homebrew install doesn't add itself to PATH in the
        # current shell right away — without this, the next `brew`
        # call below would fail with "brew: command not found" even
        # though the install just succeeded.
        if [ -x /opt/homebrew/bin/brew ]; then
            eval "$(/opt/homebrew/bin/brew shellenv)"
        fi
    fi
    brew install mysql
    brew services start mysql

    echo ""
    echo "Verifying ..."
    mysql --version
    mysql -u root -e "SELECT VERSION();"
else
    # Ubuntu / WSL2 (Ubuntu). Use `service`, not `systemctl` — most WSL2
    # distros don't run systemd by default, and `systemctl` fails there
    # ("System has not been booted with systemd"). `service` works
    # whether or not systemd is running, so it's the safer command here.
    if ! command -v apt >/dev/null 2>&1; then
        echo "ERROR: This helper supports macOS and Ubuntu/WSL2 only." >&2
        echo "For another Linux distribution, follow its MySQL package instructions." >&2
        exit 1
    fi
    sudo apt update
    sudo apt install -y mysql-server
    sudo service mysql start

    echo ""
    echo "Verifying ..."
    mysql --version
    sudo service mysql status
    sudo mysql -e "SELECT VERSION();"
fi

echo ""
echo "Install complete."
