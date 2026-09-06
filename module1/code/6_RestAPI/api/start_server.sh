#!/bin/bash

# Start PHP Development Server for Student API
# This script starts the PHP built-in server on port 8000

echo "Starting Student Management API..."
echo "API will be available at: http://localhost:8000/"
echo "Test interface will be available at: http://localhost:8000/student_api_test.html"
echo ""
echo "Press Ctrl+C to stop the server"
echo ""

# Navigate to the API directory and start server
cd "$(dirname "$0")"
php -S localhost:8000
