#!/usr/bin/env bash
set -euo pipefail

## Installing Packages for ASE 230 on WSL2

# This script is WSL2-only. Running it on Mac or native Linux would
# execute the wrong commands for that system, so refuse to continue
# if we're not actually inside WSL2.
if ! grep -qi microsoft /proc/version 2>/dev/null; then
    echo "ERROR: This script must be run inside WSL2 Ubuntu." >&2
    echo "On Mac or Linux, use the individual scripts instead:" >&2
    echo "  code/03. php installation/run.sh" >&2
    echo "  code/04. mysql installation/run.sh" >&2
    exit 1
fi

# Fix Windows line endings first, in case this script (or others you
# download later) has CRLF endings — install it before anything else
# so it's available right away. (This can't fix CRLF in THIS script if
# this script itself has that problem; if `bash run.sh` fails with
# a $'\r' error, run `dos2unix run.sh` once by hand first, then
# re-run this script.)
sudo apt update
sudo apt install -y dos2unix

sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

### PHP
# Install PHP 8.3 or later and the extensions used in ASE 230.
sudo apt install -y php php-cli php-fpm php-mysql php-zip php-mbstring \
php-xml php-curl php-bcmath php-gd php-intl php-soap unzip curl git

### MySQL
# Required for HW1 (the CLI Tools item verifies a local MySQL server).
sudo apt install -y mysql-server
sudo service mysql start
sudo service mysql status

### Composer
# Verified install: download, check its signature against the official
# one, then run it — see https://getcomposer.org/download/ "Manual"
# tab for why this matters (this script fetches and runs a remote
# PHP file, so verifying it first is worth the extra few lines).
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

echo ""
echo "Install complete."
