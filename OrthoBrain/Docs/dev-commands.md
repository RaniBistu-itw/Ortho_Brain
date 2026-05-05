# OrthoBrain — Developer Command Reference

Quick-reference for day-to-day work on the OrthoBrain Laravel project.
Working directory: `/home/admin1/Ortho_Brain/OrthoBrain`

---

## Dev Server

```bash
# Start on port 8000 (this repo's default)
php artisan serve --port=8000

# Start on port 8001 (alternate)
php artisan serve --port=8001

# Check what's holding a port
ss -ltnp | grep :8000

# Kill a specific PID
kill <pid>

# Open in browser
xdg-open http://127.0.0.1:8000
```

Login URL: `http://127.0.0.1:8000/login`

---

## Cache

```bash
# Clear everything (safe to run any time)
php artisan optimize:clear

# Individual clears
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan event:clear
```

---

## Database

```bash
# Run pending migrations
php artisan migrate

# Rollback last batch
php artisan migrate:rollback

# Fresh install + all seeders (DESTROYS all data)
php artisan migrate:fresh --seed

# Fresh install + specific seeder
php artisan migrate:fresh --seeder=DemoDataSeeder

# Run a single seeder without wiping
php artisan db:seed --class=SomeSeeder

# Show migration status
php artisan migrate:status

# MySQL quick access (empty root password locally)
mysql -u root ob1
```

Seeder dependency order:
1. Geo data (countries → states → cities → zipcodes) via `sample_data.sql`
2. `PracticesSeeder` (requires ACTIVE zipcodes)
3. `DoctorsSeeder`, `PatientsSeeder`, etc.

---

## Assets (public/ mirror pattern)

This repo serves JS/CSS directly from `public/`. There is **no Vite build step**
for custom files — edit source in `resources/`, then mirror to `public/`.

```bash
# Mirror a JS file after editing
cp resources/js/scripts/cases/add-case.js public/js/scripts/cases/add-case.js

# Mirror a specific section JS
cp resources/js/scripts/cases/sections/photographs.js \
   public/js/scripts/cases/sections/photographs.js

# Cache-bust pattern in Blade (already wired on main assets):
# {{ asset('js/scripts/cases/add-case.js') }}?v={{ @filemtime(public_path('js/scripts/cases/add-case.js')) ?: time() }}
```

Check that `add-case.blade.php` has `?v={filemtime}` on every `<link>` and
`<script>` you edit — missing cache-busts ship invisibly to cached browsers.

---

## Routes & Controllers

```bash
# List all routes
php artisan route:list

# Filter routes
php artisan route:list | grep cases
php artisan route:list | grep admin

# Show route details for a named route
php artisan route:list --name=cases.store
```

---

## Logs & Debugging

```bash
# Tail the Laravel log
tail -f storage/logs/laravel.log

# Last 50 lines
tail -50 storage/logs/laravel.log

# Clear the log
> storage/logs/laravel.log

# Check PHP version
php -v

# Dump-die in Blade
{{ dd($variable) }}

# Dump-die in a controller
dd($request->all());
```

---

## Composer & Packages

```bash
# Install / sync dependencies
# NOTE: team repo targets PHP 8.4 but this machine runs PHP 8.3.
# Use --ignore-platform-reqs to bypass the platform check.
composer install --ignore-platform-reqs

# Add a package
composer require vendor/package --ignore-platform-reqs

# Regenerate autoloader only
composer dump-autoload --ignore-platform-reqs
```

---

## Git — Team Workflow

Branch naming: `fix/<slug>` or `feat/<slug>` off `dev`.

```bash
# Fetch latest from team repo
git fetch origin

# Start a new branch off dev
git checkout dev
git pull origin dev
git checkout -b fix/my-fix

# Merge dev into your branch (keep up to date)
git fetch origin
git merge origin/dev

# Push your branch
git push -u origin fix/my-fix

# After PR is merged — clean up local branch
git branch -d fix/my-fix
git push origin --delete fix/my-fix
```

**Always merge, never rebase** on shared branches.

---

## Pre-Commit Checks

```bash
# 1. Clear caches
php artisan optimize:clear

# 2. Smoke-test the app
curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8000/login
# Expect: 200

# 3. Check for PHP syntax errors in changed files
php -l path/to/changed/file.php

# 4. Laravel Pint (if available)
./vendor/bin/pint --dirty

# 5. Pest tests (if available)
./vendor/bin/pest
```

---

## Useful Artisan Misc

```bash
# List all artisan commands
php artisan list

# Show .env-resolved config value
php artisan config:show database

# Generate app key (only on fresh installs)
php artisan key:generate

# Create a new Form Request
php artisan make:request MyFormRequest

# Create a new Controller
php artisan make:controller MyController --resource

# Create a migration
php artisan make:migration add_column_to_table --table=table_name

# Tinker (REPL)
php artisan tinker
```

---

## Environment

```bash
# See which .env is active
grep APP_ENV .env
grep APP_URL .env
grep DB_DATABASE .env

# Swap to the dev .env (if you have .env.dev and .env.main)
cp .env.dev .env
php artisan optimize:clear
```

---

## Storage & Symlinks

```bash
# Create the storage symlink (needed on fresh clone)
php artisan storage:link

# Check the symlink exists
ls -la public/storage
```

---

## Git Workflow (Standard Operating Procedure)

For daily development on the `dev` branch, follow this sequence to ensure code safety and avoid conflicts.

### 1. Sync Latest Changes
Always start by pulling the latest work from the team.
```bash
git pull origin dev
```

> [!TIP]
> **Best Practice**: For significant fixes or new features, always create a separate branch instead of working directly on `dev`.
> ```bash
> git checkout -b fix/your-fix-name  # or feat/your-feature-name
> ```

### 2. Stage & Commit
Group your changes into logical, descriptive units.
```bash
# Check status
git status

# Stage specific files
git add <filename>

# Commit with a meaningful message
git commit -m "feat/fix: descriptive message"
```

### 3. Push & Merge
If you are working on a feature branch, push the branch and open a Pull Request.

```bash
# Push your feature/fix branch
git push origin your-branch-name

# Or, if pushing directly to dev (emergency fixes only)
git push origin dev
```

### 4. Handling Conflicts
If a `git pull` results in a conflict:
1. Open the conflicted files and search for `<<<<<<< HEAD`.
2. Manually resolve the code by choosing which parts to keep.
3. Mark as resolved: `git add <filename>`.
4. Finish the merge: `git commit -m "merge: resolve conflicts"`.

---
*Last updated: 2026-05-04 — Date Input UI Unification Task*
