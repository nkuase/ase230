# Student REST API: JSON to PDO Bridge

The router and HTTP endpoints stay the same while the storage implementation
changes. This connects the PDO pattern from Week 3 to the REST API from Week 6.

## Step 1: Learn the REST structure with JSON

```bash
php -S localhost:8000
```

This is the default and uses `handlers.php` with `data/students.json`.

## Step 2: Prepare MySQL

```bash
mysql -u root -p < schema.sql
```

The Week 6 schema evolves the Week 3 `students` table to match the REST API's
fields: `name`, `email`, `major`, and `year`.

## Step 3: Switch only the storage implementation

macOS/Linux:

```bash
STUDENT_STORAGE=pdo DB_USER=root DB_PASSWORD=your_password php -S localhost:8000
```

Windows PowerShell:

```powershell
$env:STUDENT_STORAGE="pdo"
$env:DB_USER="root"
$env:DB_PASSWORD="your_password"
php -S localhost:8000
```

Optional connection settings are `DB_HOST`, `DB_PORT`, and `DB_NAME`.

## What stays unchanged

- `index.php` routing
- REST URLs and HTTP methods
- `student_api_test.html`
- cURL and JavaScript test suites

Only the handler file changes:

- JSON: `handlers.php`
- MySQL/PDO: `handlers_pdo.php` + `database.php`
