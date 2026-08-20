#!/usr/bin/env bash
set -euo pipefail

# Detect platform: mac / wsl2 / linux (native)
OS="linux"
if [ "$(uname -s)" = "Darwin" ]; then
    OS="mac"
elif grep -qi microsoft /proc/version 2>/dev/null; then
    OS="wsl2"
fi
echo "Detected platform: $OS"

if command -v code >/dev/null 2>&1; then
    echo "VS Code CLI already found: $(command -v code)"
else
    case "$OS" in
        mac)
            echo "VS Code not found — installing via Homebrew ..."
            if command -v brew >/dev/null 2>&1; then
                brew install --cask visual-studio-code
            else
                echo "ERROR: Homebrew isn't available. Install it first (https://brew.sh),"
                echo "or download VS Code directly: https://code.visualstudio.com/download"
                exit 1
            fi
            ;;
        wsl2)
            echo "You're inside WSL2. VS Code itself installs on the WINDOWS side, not"
            echo "inside this Linux shell — this script can't install it for you."
            echo ""
            echo "1. On Windows, download VS Code: https://code.visualstudio.com/download"
            echo "2. Inside VS Code, install the \"Remote - WSL\" extension."
            echo "3. Reopen this project from WSL2 with: code ."
            echo ""
            echo "Once that's done, the 'code' command will also work from this WSL2 shell."
            exit 1
            ;;
        linux)
            echo "VS Code not found. If you have snap available, this installs it:"
            if command -v snap >/dev/null 2>&1; then
                sudo snap install --classic code
            else
                echo "snap not found. Install VS Code manually for your distro:"
                echo "https://code.visualstudio.com/download"
                exit 1
            fi
            ;;
    esac
fi

code --version
echo ""
echo "Now install the \"PHP Intelephense\" and \"PHP Debug\" extensions from"
echo "the Extensions view (Ctrl+Shift+X / Cmd+Shift+X)."
