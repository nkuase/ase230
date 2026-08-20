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
    # Composer via Homebrew too — /usr/local/bin is root-owned on Mac,
    # so the manual "download + move to /usr/local/bin" install used
    # below for Linux/WSL2 would fail here with Permission denied.
    brew install php composer
    php -v
    composer --version
else
    # Linux / WSL2 (Ubuntu)
    sudo apt update
    sudo apt install -y php php-cli php-mysql php-curl php-json php-mbstring php-xml php-zip
    php -v

    ### Composer (PHP Package Manager) — verified install
    if command -v composer >/dev/null 2>&1; then
        echo "composer already installed at $(command -v composer) — skipping."
    else
        EXPECTED_SIGNATURE="$(curl -sS https://composer.github.io/installer.sig)"
        php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
        ACTUAL_SIGNATURE="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"

        if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]; then
            echo "ERROR: Composer installer signature mismatch — not installing." >&2
            rm -f composer-setup.php
            exit 1
        fi

        php composer-setup.php --quiet
        rm -f composer-setup.php
        sudo mv composer.phar /usr/local/bin/composer
        sudo chmod +x /usr/local/bin/composer
    fi
    composer --version
fi

echo ""
echo "Verifying PHP actually runs ..."
php -r 'echo "PHP works\n";'

echo ""
echo "Install complete."
