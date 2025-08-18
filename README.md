# Barangay Information System (BIS) – Gumaoc

A PHP/MySQL web app for managing barangay services and resident accounts for **Gumaoc, CSJDM, Bulacan**. The repository includes user authentication (email/password and RFID), profile management, and starter pages, with an SQL dump for the initial database schema. Key folders/files visible in the repo: `admin/`, `assets/`, `css/`, `includes/`, `pages/`, `sql/`, `user/`, `vendor/`, and root files such as `index.php`, `login.php`, `rfid-login.php`, `register.php`, `profile.php`, `gumaoc_db.sql`, `composer.json`, and `check_db.php`.

> If you’re exploring the codebase for the first time, start with the database import (`gumaoc_db.sql`) and the entry points (`login.php`, `index.php`).

---

## Features

- **User Authentication**
  - Email/Password login and registration (`login.php`, `register.php`)
  - **RFID login** endpoint (`rfid-login.php`)
- **Resident Profiles**
  - Basic profile page (`profile.php`)
- **Admin Pages**
  - Scaffolding under `admin/` for admin-facing functions
- **Starter UI**
  - Static assets under `assets/` and styles under `css/`
- **Database**
  - `gumaoc_db.sql` SQL dump for quick setup

> Note: Exact page behaviors depend on the code inside `pages/`, `admin/`, and `includes/`. Configure the DB connection first (see below), then open `index.php` or `login.php`.

---

## Tech Stack

- **PHP** (8.x recommended)
- **MySQL/MariaDB**
- **Composer** (vendor dependencies)
- **HTML/CSS (vanilla)**

---

## Getting Started

### 1) Prerequisites
- PHP 8.x, Composer, MySQL/MariaDB
- A local web server (Apache/Nginx) or use PHP built-in server

### 2) Clone
```bash
git clone https://github.com/mcdofrenchfreis/BIS-Gumaoc.git
cd BIS-Gumaoc
```

### 3) Install PHP dependencies
```bash
composer install
```

### 4) Create the database
Create a database (e.g., `gumaoc_db`) and import the dump:
```bash
mysql -u root -p -e "CREATE DATABASE gumaoc_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p gumaoc_db < gumaoc_db.sql
```

### 5) Configure DB connection
Create `includes/db.php` (if not already present) and update credentials:

```php
<?php
// includes/db.php
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';        // your password
$DB_NAME = 'gumaoc_db';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    http_response_code(500);
    die('Database connection failed: ' . $mysqli->connect_error);
}
```

You can sanity-check your connection with the provided `check_db.php` in the project root.

### 6) Run the app

**Option A: PHP built-in server**
```bash
php -S localhost:8000 -t .
```
Open: `http://localhost:8000/`

**Option B: Apache/Nginx**
- Point your virtual host’s document root to the project folder.
- Ensure PHP and mysqli extensions are enabled.

---

## Project Structure

```
BIS-Gumaoc/
├─ admin/           # Admin-facing pages
├─ assets/          # Images, icons, static files
├─ css/             # Stylesheets
├─ includes/        # DB connection & shared includes (create db.php here)
├─ pages/           # Feature pages / modules
├─ sql/             # (Optional) extra SQL files if any
├─ user/            # User-facing pages/scripts
├─ vendor/          # Composer dependencies
├─ background.jpg
├─ check_db.php
├─ composer.json
├─ composer.lock
├─ gumaoc_db.sql    # Database schema & seed data
├─ index.php        # Entry point / landing
├─ login.php
├─ logout.php
├─ profile.php
├─ register.php
└─ rfid-login.php
```

---

## Environment & Security

- **Never commit secrets** (DB passwords, API keys). Use environment variables or a local-only `includes/db.php` not tracked by Git.
- **Input validation**: Sanitize inputs and use prepared statements for queries.
- **Sessions**: Secure PHP sessions (use `session_regenerate_id(true)`, `httponly` cookies, and consider `SameSite`/`Secure` flags).
- **RFID endpoints**: Rate-limit and authenticate device traffic if exposed beyond localhost.
- **File permissions**: Ensure `vendor/` is present and web server has read access; avoid write permissions on code directories.

---

## Common Tasks

### Create an admin user
- If the SQL dump includes seed users, log in with those credentials. Otherwise:
  1. Register via `register.php`,
  2. Promote the account to admin by updating the user’s role in the database.

### Reset a user password (manual)
```sql
UPDATE users
SET password = PASSWORD('new-strong-password') -- or your app’s hashing approach (e.g., bcrypt)
WHERE email = 'user@example.com';
```
> Adjust to match your app’s hashing method in the PHP code.

---

## Troubleshooting

- **Blank page / 500 error**
  - Enable error reporting in `php.ini` (for local dev) and check your web server logs.
- **DB connection failed**
  - Verify credentials in `includes/db.php` and test `check_db.php`.
- **Missing classes**
  - Run `composer install` to restore `vendor/`.

---

## Roadmap / Ideas

- Centralized `.env` config (e.g., `vlucas/phpdotenv`) for secrets
- CSRF protection on forms
- Role-based access control for `admin/` vs `user/`
- Logging/Audit trail
- Unit tests and CI

---

## Credits

BIS – Gumaoc project by the repository authors/maintainers. See commit history and contributors in GitHub.
