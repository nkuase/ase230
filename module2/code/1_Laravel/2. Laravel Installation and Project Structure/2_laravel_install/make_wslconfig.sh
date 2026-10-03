#!/bin/bash

# Simple WSL2 .wslconfig Setup Script
# Educational version - shows core concepts

# Get Windows username
WINDOWS_USER=$(whoami.exe | cut -d'\' -f2 | tr -d '\r\n')

# Define the target path
TARGET_PATH="/mnt/c/Users/$WINDOWS_USER/.wslconfig"

if [ -f "$TARGET_PATH" ]; then
    echo "File $TARGET_PATH exists: exit"
    exit 1
fi

# Create the configuration content
cat > "$TARGET_PATH" << EOF
[wsl2]
networkingMode=mirrored
EOF

# Check if file was created successfully
if [ -f "$TARGET_PATH" ]; then
    echo "✓ Created .wslconfig for user: $WINDOWS_USER"
    echo "✓ Location: $TARGET_PATH"
    echo "Remember to restart WSL2"
    echo "In WSL2 window: cmd.exe /c 'wsl --shutdown'"
    echo "Then, restart manually."
else
    echo "❌ Failed to create file"
    exit 1
fi

