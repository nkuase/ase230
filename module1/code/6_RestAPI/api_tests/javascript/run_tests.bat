@echo off
echo ==========================================
echo REST API Testing Tool
echo ==========================================
echo.
echo Make sure your PHP API server is running:
echo   cd ../api
echo   php -S localhost:8000
echo.
echo ==========================================
echo.

REM Check if Node.js is installed
node --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Node.js is not installed or not in PATH
    echo Please install Node.js from https://nodejs.org
    pause
    exit /b 1
)

echo Running API tests...
echo.
node test_runner.js

echo.
echo ==========================================
echo Test completed!
echo ==========================================
pause