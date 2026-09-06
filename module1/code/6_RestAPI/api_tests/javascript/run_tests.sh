#!/bin/bash

echo "=========================================="
echo "REST API Testing Tool"
echo "=========================================="
echo ""
echo "Make sure your PHP API server is running:"
echo "  cd ../api"
echo "  php -S localhost:8000"
echo ""
echo "=========================================="
echo ""

# Check if Node.js is installed
if ! command -v node &> /dev/null; then
    echo "ERROR: Node.js is not installed or not in PATH"
    echo "Please install Node.js from https://nodejs.org"
    exit 1
fi

echo "Running API tests..."
echo ""
node test_runner.js

echo ""
echo "=========================================="
echo "Test completed!"
echo "=========================================="