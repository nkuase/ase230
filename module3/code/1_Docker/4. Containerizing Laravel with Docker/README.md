# Laravel with Docker (Module 2: Bearer Token APIs)

This folder automates Module 2's `5. Bearer Token APIs` completely in Docker. Names are the same, except the two shell scripts, which get a `-docker` suffix:

| Module 2 | This folder |
|---|---|
| `run1-5.sh` | `run1-5-docker.sh` (Docker version) |
| `student-api1-5/` (lesson files) | `student-api1-5/` (identical copy) |
| `student-api/` (created project) | `student-api/` (created project) |
| `test1-5.sh` | `test1-5-docker.sh` (port 8000, php and mysql in containers) |
| `run1-5-tests/index.html` | `run1-5-tests/index.html` (default URL is port 8000) |

```bash
cp -r "4. Containerizing Laravel with Docker" my-project   # copy the WHOLE folder: it has hidden .env files
cd my-project
bash run1-5-docker.sh
```

Requirements: Docker Desktop (Windows: run from WSL2/Ubuntu). PHP, Composer, and MySQL are **not** needed on your computer.

## Three containers

```
Browser :8000 -> nginx :80 -> php (PHP-FPM) :9000 -> mysql :3306
```

| Service | Image | Role |
|---|---|---|
| `nginx` | `nginx:alpine` + `nginx.conf` | Web server; root is `/var/www/html/public` |
| `php` | `php:8.4-fpm` + Composer | Runs Laravel 13 (needs PHP 8.3+) |
| `mysql` | `mysql:8.0` | Database `studentdb`, user `ase230` (named volume `mysql-data`) |

`./student-api` is mounted at `/var/www/html` in both `nginx` and `php`, the same pattern as the previous `simple-php-nginx` example (host port 8000 too, so run only one at a time).

## What run1-5-docker.sh does

The same steps as Module 2's `run1-5.sh`:

1. Builds the images (nginx, php, mysql).
2. Creates the Laravel 13 project `student-api/` (`composer create-project`, inside the container).
3. Writes `student-api/.env` from `.env.docker`, installs dependencies, generates the app key.
4. Runs `php artisan install:api` (Laravel Sanctum + `personal_access_tokens` migration).
5. Copies the lesson files from `student-api1-5/` (the same eight files as Module 2).
6. Starts the three containers (`docker compose up -d --wait`).
7. Runs the migrations (skipping tables that already exist), `StudentSeeder`, `UserSeeder`, and `route:list --path=api`.

It can be run again safely: existing projects are kept, migrations and seeders skip existing data.

## Test it

```bash
bash test1-5-docker.sh                 # same tests as Module 2's test1-5.sh
# or open run1-5-tests/index.html in your browser and click the test buttons
```

Demo users: `student@university.edu` / `student123` and `teacher@university.edu` / `teacher456`.

## Differences from the local Module 2 setup

| Module 2 (local) | Docker |
|---|---|
| MySQL on `127.0.0.1`, user created with `sudo mysql` | `mysql` container; `.env` creates database `studentdb` and user `ase230` |
| `php artisan serve --port=8080` | nginx on port 8000 |
| `DB_HOST=127.0.0.1` | `DB_HOST=mysql` (the Compose service name) |
| Migrations run one by one; a table that already exists is skipped | Same logic (`table_exists` through the `mysql` container) |
| `test1-5.sh` calls `php` and `mysql` on your computer | Same commands through `docker compose exec` |

The Laravel code (`student-api1-5/`) is identical. Two environment files: `.env` is read by Docker Compose (MySQL initialization), `.env.docker` becomes `student-api/.env` (Laravel). Keep `MYSQL_*` and `DB_*` values consistent. The supplied passwords are for local classroom use only.

## Commands

```bash
docker compose ps                                  # three services should be healthy
docker compose logs -f                             # follow logs
docker compose exec php php artisan route:list --path=api
docker compose down                                # stop; the database is kept
docker compose down -v                             # stop and DELETE the database
```

`MYSQL_*` values are used only when MySQL creates a **new** volume; changing them later needs `docker compose down -v`.

## Permissions

`./student-api` is bind-mounted over `/var/www/html`, so a `chown` done while building the image is hidden. `run1-5-docker.sh` runs `chown -R www-data:www-data storage bootstrap/cache` in the container so PHP-FPM can write there.

## Common issues

- **Port 8000 already in use:** stop the previous example (`docker compose down` in `simple-php-nginx`) or change `"8000:80"` in `docker-compose.yml` (and the URL in `test1-5-docker.sh` and `run1-5-tests/index.html`).
- **`Table 'personal_access_tokens' already exists` (error 1050):** `run1-5-docker.sh` now skips existing tables. If an older copy of the script fails, run `docker compose down -v` (deletes the database) and start again.
- **Container will not start:** `docker compose logs mysql php nginx`; rebuild with `docker compose build --no-cache`.
- **Start over completely:** `docker compose down -v`, delete the `student-api/` folder, then `bash run1-5-docker.sh`.
- **`.env` or `.env.docker` missing:** you copied `folder/*` instead of the folder; they are hidden files.

## Project 2

`run1-5-docker.sh` copies the lesson files into `student-api/` and would **overwrite** same-named files of your own project. For your Project 2 app, do not run it: put your Laravel app (without `vendor/` and your local `.env`) in `student-api/`, copy `.env.docker` to `student-api/.env`, and run:

```bash
docker compose up -d --wait
docker compose exec php composer install
docker compose exec php php artisan key:generate
docker compose exec php php artisan migrate --force
```
