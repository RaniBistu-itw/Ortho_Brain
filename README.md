<div align="center">

# 🦷 OrthoBrain

**Orthodontic Case Management Platform for Modern Dental Practices**

[![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-4-FB70A9?style=flat-square&logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![Vite](https://img.shields.io/badge/Vite-8-646CFF?style=flat-square&logo=vite&logoColor=white)](https://vitejs.dev)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://mysql.com)
[![License](https://img.shields.io/badge/License-Proprietary-red?style=flat-square)](#)

*A full-featured platform for orthodontic case submission, review, and production coordination — built for doctors and admins alike.*

</div>

---

## 📋 Table of Contents

- [What is OrthoBrain?](#-what-is-orthobrain)
- [Tech Stack](#-tech-stack)
- [System Requirements](#-system-requirements)
- [Project Structure](#-project-structure)
- [Quick Start](#-quick-start)
- [Environment Configuration](#-environment-configuration)
- [Database](#-database)
- [Running the Project](#-running-the-project)
- [Default Credentials](#-default-credentials)
- [User Roles & Workflows](#-user-roles--workflows)
- [Case Wizard](#-case-wizard-10-sections)
- [Case States](#-case-states)
- [AI Services](#-ai-services-in-progress)
- [Artisan Commands](#-common-artisan-commands)
- [Testing](#-testing)
- [Critical Conventions](#%EF%B8%8F-critical-conventions)
- [Troubleshooting](#-troubleshooting)

---

## 🦷 What is OrthoBrain?

OrthoBrain is a case-management platform for orthodontic practices. Doctors submit treatment cases through a **multi-section wizard**; admins review and approve those cases, then coordinate production and shipping.

```
Doctor submits case → Admin reviews → Production → Shipping
```

---

## 🛠 Tech Stack

| Layer | Technology | Notes |
|---|---|---|
| Language | PHP 8.3+ | Laravel 13 framework |
| Frontend | Alpine.js | CDN-loaded; drives all in-page reactivity |
| CSS / UI | Bootstrap Vuexy | Hand-managed in `public/`; not rebuilt by Vite |
| Build Tool | Vite 8 | Outputs to `public/build/`; covers app-specific JS/CSS |
| PDF Export | dompdf 3.1 | Server-rendered case reports |
| Database | MySQL / MariaDB | 44 migrations organised by domain |
| AI Vision | Google Gemini *(in progress)* | Photo QC & smile plan — not yet in production |
| Email | SMTP / Mailpit | Verification, approval/rejection, password reset |
| Testing | Pest 4.6 | Feature + unit tests |
| Dev Runner | `composer dev` | Starts serve + queue + pail + Vite concurrently |

---

## 💻 System Requirements

| Requirement | Minimum Version |
|---|---|
| PHP | ≥ 8.3 |
| Composer | ≥ 2.6 |
| Node.js | ≥ 18 (LTS recommended) |
| npm | ≥ 9 |
| MySQL / MariaDB | ≥ 8.0 |
| Git | Latest |

**Required PHP Extensions:** `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd`

---

## 📁 Project Structure

```
OrthoBrain/
├── app/
│   ├── Http/
│   │   ├── Controllers/         # Doctor-side controllers
│   │   ├── Controllers/Admin/   # Admin-side controllers (parity with doctor)
│   │   ├── Controllers/AI/      # Image analysis, smile preview (in progress)
│   │   └── Controllers/Auth/    # Login, register, OTP, password reset
│   └── Models/                  # Eloquent models
├── database/
│   ├── factories/               # Model factories
│   ├── migrations/              # 44 schema migrations
│   └── seeders/                 # Seeders + sql/sample_data.sql
├── resources/
│   ├── css/                     # Tailwind sources
│   ├── js/scripts/cases/        # Alpine-driven JS (must mirror public/)
│   └── views/
│       ├── content/cases/       # Case wizard Blade templates
│       └── admin/               # Admin views
├── public/
│   ├── js/scripts/cases/        # Live copies loaded by Blade <script> tags
│   └── build/                   # Vite output (do not hand-edit)
├── routes/
│   └── web.php                  # All HTTP routes (/dev/* + /admin/* + public)
├── tests/                       # Pest tests (Feature + Unit)
├── Docs/architecture/           # Deep-dive architecture docs (01–09)
├── CLAUDE.md                    # Conventions and gotchas — read first
├── .env.example
├── artisan
├── composer.json
├── package.json
└── vite.config.js
```

---

## 🚀 Quick Start

```bash
# 1. Clone the repository
git clone <repository-url> OrthoBrain
cd OrthoBrain

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Copy environment file
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Create the database
mysql -u root -p -e "CREATE DATABASE ob1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 7. Run migrations + seed
php artisan migrate --seed

# 8. Link storage
php artisan storage:link

# 9. Build frontend assets
npm run build
```

> **Shortcut:** `composer setup` runs steps 2–9 in a single command.

---

## ⚙️ Environment Configuration

Copy `.env.example` to `.env` and configure the following sections:

### Application
```env
APP_NAME=OrthoBrain
APP_ENV=local
APP_KEY=                          # generated by `php artisan key:generate`
APP_DEBUG=true
APP_URL=http://localhost:8000
```

### Database
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ob1
DB_USERNAME=root
DB_PASSWORD=
```

### Sessions / Cache / Queue
```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database
```

### Mail
```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS="no-reply@orthobrain.local"
MAIL_FROM_NAME="${APP_NAME}"
```

### Seeded Credentials
```env
SUPER_ADMIN_EMAIL=admin@orthobrain.local
SUPER_ADMIN_PASSWORD=Password@1

TEST_DOCTOR_EMAIL=doctor@orthobrain.local
TEST_DOCTOR_PASSWORD=Password@1
```

### Branding
```env
ADMIN_BRAND_NAME=orthobrain
ADMIN_BRAND_TAGLINE="Orthodontics for Your Dental Practice"
```

### Google reCAPTCHA v3
```env
RECAPTCHA_SITE_KEY=
RECAPTCHA_SECRET_KEY=
RECAPTCHA_MIN_SCORE=0.5
# Leave blank on offline localhost — middleware logs a warning and lets requests through
```

### AI Vision *(In Progress — not yet in production)*
```env
AI_PROVIDER_CHAIN=gemini,ollama,canned

# Primary — Google Gemini
GEMINI_API_KEY=
AI_GEMINI_MODEL=gemini-2.0-flash

# Fallback — Local Ollama
AI_OLLAMA_URL=http://localhost:11434
AI_OLLAMA_QC_MODEL=moondream
AI_OLLAMA_SMILE_MODEL=llava:7b

# Image Edit (Before/After Visualisation)
AI_IMAGE_EDIT_MODEL=gemini-2.5-flash-image
```

---

## 🗄 Database

### Migrations

The project ships **44 migrations** organised by domain. Run them all with:

```bash
php artisan migrate
```

<details>
<summary><strong>View Full Migration List (44 files)</strong></summary>

| # | File | Purpose |
|---|---|---|
| 1 | `create_users_table` | Core users (auth) |
| 2 | `create_cache_table` | Cache store |
| 3 | `create_jobs_table` | Queue jobs |
| 4 | `create_admins_table` | Admin profiles |
| 5 | `create_doctors_table` | Doctor profiles |
| 6 | `create_countries_table` | Countries master |
| 7 | `create_states_table` | States master |
| 8 | `create_cities_table` | Cities master |
| 9 | `create_zipcodes_table` | Zipcodes master |
| 10 | `create_products_category_table` | Product categories |
| 11 | `create_products_subcategory_table` | Product sub-categories |
| 12 | `create_products_table` | Products |
| 13 | `create_scanners_table` | Scanner devices |
| 14 | `create_doctor_addresses_table` | Doctor addresses |
| 15 | `create_modalities_table` | Modalities master |
| 16 | `create_doctor_modalities_table` | Doctor ⇄ modality pivot |
| 17 | `create_buccal_corridor_options_table` | Buccal corridor options |
| 18 | `create_doctor_buccal_corridors_table` | Doctor ⇄ buccal pivot |
| 19 | `create_treatment_modalities_table` | Treatment modalities |
| 20 | `create_doctor_treatment_modalities_table` | Doctor ⇄ treatment pivot |
| 21 | `create_specialties_table` | Specialties master |
| 22 | `create_doctor_specialties_table` | Doctor ⇄ specialty pivot |
| 23 | `create_cases_table` | Cases |
| 24 | `create_prescriptions_table` | Prescriptions |
| 25 | `create_prescription_tooth_restrictions_table` | Prescription tooth restrictions |
| 26 | `create_practices_table` | Practices |
| 27 | `add_practice_id_to_doctors_table` | Link doctors ⇄ practice |
| 28 | `create_product_images_table` | Product images |
| 29 | `add_logo_path_to_practices_table` | Practice logo column |
| 30 | `create_doctor_practice_table` | Doctor ⇄ practice pivot |
| 31 | `create_notifications_table` | Notifications |
| 32 | `add_practice_id_to_cases_table` | Link cases ⇄ practice |
| 33 | `add_suspended_to_doctor_practice_enum` | Suspended status enum |
| 34 | `normalise_practice_phone_country_codes` | Phone code normalisation |

</details>

### Seeders

Seeders run in this order via `DatabaseSeeder`:

| Order | Seeder | What it populates |
|---|---|---|
| 1 | `LocationMasterSeeder` | Countries, states, cities, zipcodes |
| 2 | `ManageTypesSeeder` | Product categories, subcategories, products, scanners |
| 3 | `SuperAdminSeeder` | Super admin user |
| 4 | `MasterOptionsSeeder` | Modalities, buccal corridors, treatment modalities, specialties |
| 5 | `PracticesSeeder` | Sample practices |
| 6 | `DoctorSeeder` | Test doctor |
| 7 | `CaseDemoSeeder` | Demo cases for test doctor |
| 8 | `CaseDashboardSeeder` | Cases for dashboard widgets |

> **Auxiliary:** `database/seeders/sql/sample_data.sql` — raw SQL bundle for direct import.

```bash
# Run all seeders
php artisan db:seed

# Run a single seeder
php artisan db:seed --class=SuperAdminSeeder

# Fresh migrate + seed
php artisan migrate:fresh --seed
```

### Migration Commands

```bash
php artisan migrate               # Run pending migrations
php artisan migrate:rollback      # Rollback last batch
php artisan migrate:fresh         # Drop all + re-run (DESTRUCTIVE)
php artisan migrate:fresh --seed  # Drop, re-run, then seed
php artisan migrate:status        # Show migration status
```

---

## ▶️ Running the Project

### Option A — All-in-one (Recommended)

Spawns server + queue + log tail + Vite in parallel:

```bash
composer dev
```

This runs:
- `php artisan serve` — HTTP server
- `php artisan queue:listen --tries=1 --timeout=0` — queue worker
- `php artisan pail --timeout=0` — live log viewer
- `npm run dev` — Vite HMR

### Option B — Separate Terminals

```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev

# Terminal 3 (only if using queued jobs)
php artisan queue:listen
```

App runs at **http://localhost:8000**

### Production Build

```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🔑 Default Credentials

> Seeded by `SuperAdminSeeder` and `DoctorSeeder` — override via `.env`

| Role | Email | Password |
|---|---|---|
| Super Admin | `admin@orthobrain.local` | `Password@1` |
| Test Doctor | `doctor@orthobrain.local` | `Password@1` |

---

## 👥 User Roles & Workflows

Doctors and admins share a single `users` table with a `role` enum. Note: `users.id ≠ doctors.id` — always resolve via `currentDoctor()->id`.

| Role | Entry Point | Middleware | Responsibilities |
|---|---|---|---|
| Doctor | `/dev/*` | `auth` | Create cases, upload media, track status, manage profile |
| Admin | `/admin/*` | `admin` | Review cases, approve/reject doctors, manage master data |
| Public | `/login`, `/register` | — | Authentication and registration only |

### Doctor Onboarding

```
Register → Verify Email (OTP) → Admin Approves → Join Practice → Dashboard
```

| Step | URL | What happens |
|---|---|---|
| 1. Register | `GET /register` | Email + password + practice selection |
| 2. Verify email | `GET /verify-email` | OTP sent; must confirm before login |
| 3. Admin approves | Admin dashboard | Approval email sent to doctor |
| 4. Join practice | `/dev/practices` | Doctor joins primary practice (required) |
| 5. Dashboard | `/dev/dashboard` | Main entry; visible only with active practice |

---

## 🧙 Case Wizard (10 Sections)

Each section saves independently. The final submit only transitions `DRAFT → SUBMITTED` — it does not re-save section data.

| # | Section | Key Data |
|---|---|---|
| 1 | Patient Information | Name, DOB, chart ID, phone |
| 2 | Prescription | Arch, IPR, attachments, elastics, extractions |
| 3 | Additional Information | Checklist (JSON) |
| 4 | Impressions | Scanner type, impression method |
| 5 | Photographs | Up to 9 tiles, drag-reorder |
| 6 | X-Rays | Tile-based upload, drag-reorder |
| 7 | Shipping Address | To/from, geo lookup |
| 8 | Submit Order | Review + confirm |

### Media Upload Rules

| Scenario | Correct Action |
|---|---|
| New file from disk | Open picker → crop modal → upload blob |
| Drag-reorder, both tiles have blobs | Destroy + upload (swap) |
| Drag-reorder, one or both tiles are URL-only | Call `POST /media/reorder` (server-side swap) |
| Swap when URL-only tile exists | Fetch URL → blob first, then swap |

> ⚠️ **Never destroy + re-upload a URL-only tile — silent data loss.** (See `CLAUDE.md` Entry 11)

---

## 🔄 Case States

| Status | Who Sets It | Doctor Can Edit? | Admin Can Edit? |
|---|---|---|---|
| `DRAFT` | Created automatically | ✅ Yes | ❌ No |
| `SUBMITTED` | Doctor (submit step) | ❌ No | ✅ Yes |
| `IN_REVIEW` | Admin | ❌ No | ✅ Yes |
| `APPROVED` | Admin | ❌ No | ❌ No |
| `REJECTED` | Admin | ❌ No | ❌ No |

---

## 🤖 AI Services *(In Progress)*

> **These features are under active development and not yet in production.**

| Provider | Role | Trigger |
|---|---|---|
| Google Gemini | Photo QC + smile plan generation | `POST /photos/classify`, `POST /smile-plan/generate` |
| Ollama (local) | Vision fallback if Gemini fails | Automatic |
| Canned (mock) | Final fallback if Ollama unavailable | Configured in `config/ai.php` |
| Smile Preview | Before/after image generation | `POST /smile-preview/generate` (rate-limited 5/min) |

Provider chain: `gemini → ollama → canned`

---

## 🔧 Common Artisan Commands

```bash
# Cache management
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear        # Runs all of the above

# Storage
php artisan storage:link

# Development
php artisan tinker                # REPL
php artisan route:list            # Show all routes
php artisan pail                  # Live log viewer

# Queue
php artisan queue:work --once     # Run queue worker once
php artisan queue:listen          # Continuous queue listener
```

---

## 🧪 Testing

```bash
# Run the full Pest test suite
php artisan test

# Or using composer
composer test
```

Tests live in `tests/` and are split into `Feature` and `Unit`.

---

## ⚠️ Critical Conventions

> Full explanations live in `CLAUDE.md`. This is a quick-scan reference only.

| # | Rule | Where it matters |
|---|---|---|
| 1 | Use `currentDoctor()->id`, never `Auth::id()` | Any query scoped by `doctor_id` |
| 2 | Doctor controller changes must also update Admin controller | Case edit, section saves, prefill |
| 3 | Sync `resources/js/` and `public/js/` in the same commit | All case wizard JS |
| 4 | Audit all four layers: model → controller → JS → Blade | Any form-bound data change |
| 5 | Country field holds ISO code (`US`), not display name | Shipping address serialization |
| 6 | Wrap `<select>` assignments in `$nextTick` when options come from `x-for` | Alpine state init |
| 7 | Restart `php artisan serve` after every `git pull` | Any unexplained stale behavior |
| 8 | Every column change needs full round-trip audit | Migrations, model fillable, serializers |
| 9 | Every user-facing PR needs a manual smoke test in the PR description | All feature PRs |
| 10 | Verify user-facing validation feedback at every rejection mode | New validated fields |
| 11 | **Never destroy + re-upload a URL-only media tile** | Media swap / drag-reorder |

---

## 🔍 Troubleshooting

**Stale behavior after `git pull`**
```bash
php artisan serve   # Always restart the dev server after pulling
```

**Assets not updating**
```bash
npm run build       # Rebuild frontend assets
php artisan optimize:clear
```

**Queue jobs not running**
```bash
php artisan queue:listen --tries=1
```

**Database issues**
```bash
php artisan migrate:status        # Check migration state
php artisan migrate:fresh --seed  # Nuclear option — resets everything
```

---

## 📚 Further Reading

| Question | Resource |
|---|---|
| How does the case wizard work end-to-end? | `Docs/architecture/04-add-case-flow.md` |
| What's the full database schema? | `Docs/architecture/03-database-schema.md` |
| How does the frontend pipeline work? | `Docs/architecture/05-frontend-pipeline.md` |
| How are doctor and admin controllers kept in sync? | `Docs/architecture/06-admin-parity.md` |
| What are all the gotchas I need to know? | `CLAUDE.md` |

---

<div align="center">

Built with ❤️ for orthodontic practices · **OrthoBrain** · *Orthodontics for Your Dental Practice*

</div>
