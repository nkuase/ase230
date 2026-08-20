#!/usr/bin/env bash
set -e

# Always run relative to course_tools/ (one level up from this script,
# which lives in course_tools/code/), matching marp.ini's paths
# (input=./lecture, output=./pdf).
cd "$(dirname "$0")/.."

if ! command -v marp >/dev/null 2>&1; then
    echo "marp not found — installing via Homebrew ..."
    if command -v brew >/dev/null 2>&1; then
        brew install marp-cli
    else
        echo "ERROR: marp-cli is required to build the PDF slides, but"
        echo "Homebrew isn't available to install it automatically."
        echo "Install Homebrew first (https://brew.sh), then re-run this script."
        echo "Or install marp-cli yourself: https://github.com/marp-team/marp-cli"
        exit 1
    fi
else
    echo "marp found: $(command -v marp)"
fi

# PDF conversion needs a Chromium-family browser (Chrome, Edge, or
# Chromium itself) — marp-cli doesn't install one for you.
if [ ! -d "/Applications/Google Chrome.app" ] \
   && [ ! -d "/Applications/Microsoft Edge.app" ] \
   && [ ! -d "/Applications/Chromium.app" ]; then
    echo "WARNING: no Chrome/Edge/Chromium found in /Applications."
    echo "PDF conversion needs one of these browsers installed."
fi

mkdir -p pdf

echo "Building PDFs from ./lecture into ./pdf ..."
marp --input-dir ./lecture --output ./pdf --pdf --allow-local-files --theme default

echo ""
echo "Done. PDFs are in course_tools/pdf/:"
ls -1 pdf/*.pdf 2>/dev/null || echo "(no PDFs produced — check the marp output above for errors)"
