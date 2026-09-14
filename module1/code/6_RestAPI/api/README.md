# Student REST API: Setup & Automation Guide

An educational REST API written in PHP demonstrating how a consistent HTTP interface can be backed by two interchangeable storage implementations: **JSON flat-file** or **MySQL via PDO**.

```txt
                    ┌─ handlers.php ────── data/students.json
HTTP Request ───► index.php ──┤
                    └─ handlers_pdo.php ── MySQL (studentdb)
```

### API Endpoints

| Method | Endpoint | Description | Request Body |
|---|---|---|---|
| `GET` | `/` | Show API information and available endpoints | None |
| `GET` | `/students` | List all students | None |
| `GET` | `/students/{id}` | Get single student by ID | None |
| `POST` | `/students` | Create new student | `{"name": "...", "email": "...", "major": "...", "year": 1}` |
| `PUT` | `/students/{id}` | Replace existing student | All 4 fields required |
| `DELETE` | `/students/{id}` | Delete student by ID | None |

---

## 1. Quick Start

Get the entire development environment and API server running in three commands:

```bash
# From the 6_RestAPI directory:
cd api

# One-time setup: install packages, configure database, and verify tools
./ensure_install_packages.sh

# Start the API server in default JSON mode
./start_server.sh
```

- **Keep this terminal window open** while the server is running.
- **Open the test UI** in your browser: `http://localhost:8080/student_api_test.html`
- **To stop the server**: Press `Ctrl+C` in the server terminal, or open a second terminal and run `./stop_server.sh`.

> [!NOTE]
> The first setup may take several minutes. macOS or Ubuntu may ask for your computer administrator password (`sudo`). On macOS, an existing protected MySQL installation may also require its MySQL root password.

---

## 2. Daily Development Workflow

Choose either storage mode to run your application:

### Option A: Run with JSON Storage
```bash
./start_server.sh
```
- Uses `handlers.php` and stores records in `data/students.json`.
- By default, automatically resets `data/students.json` to `[]` on startup so every test run starts clean.
- To preserve existing records across restarts:
  ```bash
  ./start_server.sh --no-reset
  ```

### Option B: Run with MySQL/PDO Storage
```bash
./start_server.sh --pdo
# or:
./start_server.sh --mysql
```
- Automatically starts the MySQL service if stopped.
- Connects using the dedicated course user (`ase230`), recreates a clean `studentdb` database, and imports table structures from `schema.sql`.
- Switches `STUDENT_STORAGE=pdo` and routes requests through `handlers_pdo.php`.
- To preserve existing database records across restarts:
  ```bash
  ./start_server.sh --pdo --no-reset
  ```

> [!NOTE]
> **Safe `--no-reset` Handling**: You can safely pass `--no-reset` at any time, even on your very first run. The script operates defensively:
> - **MySQL (`--pdo`)**: Ensures the `students` table exists by executing `schema.sql` (`CREATE TABLE IF NOT EXISTS`). If the table and records already exist, MySQL keeps them completely intact; if running for the first time, the schema is created automatically.
> - **JSON**: Ensures `data/students.json` exists (creating an empty list `[]` only if the file is missing) without overwriting existing data.
> - To wipe existing data and start completely fresh, simply omit `--no-reset` or run `./reset_db.sh`.

### Resetting Data Between Test Runs
If you want to clear test data while the server is running without restarting it:
```bash
# Reset only JSON storage (data/students.json -> []):
./reset_db.sh

# Reset both JSON storage and MySQL database (studentdb):
./reset_db.sh --mysql
```

### Stopping Server and Background Services
When finished working, stop the development server and background database service cleanly:
```bash
# Stop both PHP development server (port 8080) and MySQL service:
./stop_server.sh

# Stop only the PHP server (leaves MySQL running):
./stop_server.sh --php

# Stop only the MySQL service (leaves PHP running):
./stop_server.sh --mysql
```

---

## 3. Testing the API

Once the server is running at `http://localhost:8080`, test endpoints using either the browser UI or the automated test suite.

### Option A: Web Test UI (Browser)
Open the visual test page in your web browser:
```txt
http://localhost:8080/student_api_test.html
```

> [!CAUTION]
> **Never open `student_api_test.html` directly from your file manager (`file:///...`)!**  
> Browser security (CORS) treats local `file://` files as untrusted origins and may block requests to `http://localhost:8080`. You must open it through the HTTP server URL above.

### Option B: Automated cURL Test Runner
From the `api/` directory, navigate to the cURL test suite:
```bash
# From the api/ directory:
cd ../api_tests/curl
./test_runner_curl.sh
```
This tests all 16 endpoint scenarios, including CRUD operations, validation failures (400), unknown resources (404), and method restrictions (405 with `Allow` headers).

---

## 4. How the Automation Scripts Work

The scripts in this directory provide repeatable setup automation without manual package management.

### `start_server.sh`
- **Orchestrator**: Sources `ensure_install_packages.sh` to load environment variables and helper functions.
- **Dependency Guard**: Calls `install_mac_packages` or `install_ubuntu_packages` to ensure required binaries are installed.
- **Service & DB Guard**: In PDO mode, ensures MySQL daemon is running (`start_mysql`), ensures database/user exists (`configure_course_database`), and verifies connectivity (`verify_installation`).
- **Safety**: Uses `set -euo pipefail` to halt immediately if database reset or setup fails.
- **Runtime**: Runs `php -S localhost:8080`.

### `reset_db.sh`
- **State Hygiene**: Clears stale data to prevent failures caused by records from previous test runs.
- **JSON**: Writes `[]` to `data/students.json`.
- **MySQL**: Drops and recreates `studentdb`, then imports `schema.sql`.
- **Safety**: Uses `set -euo pipefail` and explicit error checks on schema import. If database recreation fails, it exits with an error and never prints `Done.`.

### `stop_server.sh`
- **Port Cleanup**: Uses `pkill -f "php -S.*8080"` and `lsof` to find and terminate processes listening on port 8080.
- **Service Teardown**: Gracefully stops the MySQL daemon via Homebrew (`brew services stop mysql`) or system service (`service mysql stop`).

### `ensure_install_packages.sh`
- **Platform Detection**: Identifies macOS (`Darwin`) and evaluates Homebrew shellenv (`/opt/homebrew` or `/usr/local`). Identifies Ubuntu Linux / WSL2 via `/etc/os-release` (`ID=ubuntu`).
- **Batch Installation**:
  - macOS: Checks `php` and `mysql` separately with `brew list --versions`, then installs whichever formula is missing.
  - Ubuntu: Uses `apt-get install -y php-cli php-mysql mysql-server default-mysql-client`.
- **Service Health (`is_mysql_running`)**: Uses `mysqladmin ping` exit code to confirm MySQL daemon responsiveness independently from user login.
- **Dedicated Course Account (`configure_course_database`)**:
  - Creates database `studentdb` and user `'ase230'@'localhost'`.
  - Uses `ALTER USER` to synchronize the password (`ase230pass`) and `GRANT` to synchronize permissions on `studentdb.*`.
  - **Does not modify system `root`**, protecting existing personal projects and system databases.
- **Strict 4-Step Verification (`verify_installation`)**:
  Fails fast with exit code `1` if any check fails:
  1. `php -v` (PHP CLI execution)
  2. `class_exists('PDO')` (PDO extension available)
  3. `extension_loaded('pdo_mysql')` (PDO MySQL driver loaded)
  4. `mysql -u ase230 studentdb -e "SELECT 1;"` (database connectivity verified)

---

## 5. Security & Configuration Notes

- **Dedicated User**: The application uses `'ase230'@'localhost'` with password `'ase230pass'`. This configuration is intended **strictly for local educational lab environments** for ease of learning; it should never be used in a production environment.
- **Password Handling**: Scripts use `MYSQL_PWD` instead of placing the password directly in command-line arguments. Note that environment variables may still be visible in shared process environments, which is acceptable for local single-user development.
- **Fail-Fast Error Handling**: The setup, server startup, and database reset scripts use `set -euo pipefail` so any command failure immediately stops execution rather than failing silently.

---

## 6. Troubleshooting & Complete Reinstallation

### Common Issues & Quick Fixes

Before considering reinstallation, check if your issue matches one of these common errors:

- **`Permission denied: ./...sh`**: The shell scripts may need executable permissions. Run:
  ```bash
  chmod +x *.sh
  ```
- **`Address already in use` (Port 8080)**: A previous PHP server process is holding port 8080. Stop it with:
  ```bash
  ./stop_server.sh --php
  ```
- **MySQL Connection / Credentials Error**: If the database service stopped or credentials fell out of sync, re-run:
  ```bash
  ./ensure_install_packages.sh
  ```
- **Web page does not load in browser**: Ensure the server is actively running (`./start_server.sh`) and navigate to `http://localhost:8080/student_api_test.html`. Do not open the HTML file directly from your operating system's file manager (`file:///...`).
- **If problems persist**: Only after the steps above fail should you consider the complete reinstallation below.

---

### Complete Reinstallation (Advanced / Emergency Only)

> [!CAUTION]
> **DANGER: Read carefully before running `ensure_delete_packages.sh`!**  
> This script is an **emergency recovery tool**, NOT part of the normal daily workflow.  
> Running `ensure_delete_packages.sh` permanently uninstalls PHP and MySQL, and **deletes all local MySQL databases, configuration files, and data directories managed by Homebrew/APT**, not just `studentdb`. Other personal or course projects using local MySQL will be permanently lost.

Use this script only if your PHP or MySQL installation is severely corrupted and you need to reset to a clean state.

```bash
# 1. Stop any running PHP server first
./stop_server.sh

# 2. Run the interactive uninstaller
./ensure_delete_packages.sh
```

- **Safety Prompt**: In interactive mode, the script requires you to type `DELETE` in all capital letters before proceeding.
- **What It Purges**:
  - Stops MySQL service.
  - macOS: Runs `brew uninstall --force --ignore-dependencies` against `mysql`, `php`, and related packages (`php@...`, `mysql@...`, `mysql-client`, `mysql-client@...`). Removes `$brew_prefix/var/mysql`, `$brew_prefix/etc/php`, `$brew_prefix/etc/my.cnf`, and `$brew_prefix/etc/my.cnf.d`.  
    *(Warning: `--ignore-dependencies` may break other local tools that depend on Homebrew's PHP or MySQL).*
  - Ubuntu: Purges all matching packages (`php`, `php-*`, `php[0-9]*`, `mysql-*`, `default-mysql-*`) and deletes `/var/lib/mysql`, `/etc/mysql`, and `/etc/php`.
- **Post-Deletion Verification**: Clears command cache (`hash -r`) and verifies that `php`, `mysql`, `mysqld`, and `mysqladmin` are no longer in `PATH`.
- *(The `--yes` flag is reserved strictly for disposable CI/CD containers and virtual machines).*

After deletion, run `./ensure_install_packages.sh` to perform a fresh Homebrew/APT-managed installation.
