> _Last verified: against origin/dev @ `096aea5` on 2026-05-06. If editing, update this stamp._

# 01 — Tech Stack

Authoritative versions live in [composer.json](../../composer.json) and
[package.json](../../package.json). This page summarises *what runs
where* and *why* each piece is in the stack.

## Backend

| Piece | Version | Purpose | Notes |
|---|---|---|---|
| PHP | `^8.3` | Runtime | Local dev is on 8.3. The team's canonical env runs PHP 8.4 — see the `project_php_version_gotcha` memory note for the symfony/var-dumper trap and the [bootstrap/app.php](../../bootstrap/app.php) fallback. |
| Laravel | `^13.0` | Framework | Routing in [routes/web.php](../../routes/web.php), bootstrap in [bootstrap/app.php](../../bootstrap/app.php). |
| Livewire | `^4.2` | Server-rendered components | Currently minimal use; most interactivity is Alpine inside Blade. |
| Tinker | `^3.0` | REPL | `php artisan tinker`. |
| barryvdh/laravel-dompdf | `^3.1` | PDF export | Used by [CasePdfController](../../app/Http/Controllers/CasePdfController.php) → [pdf/case-report.blade.php](../../resources/views/content/cases/pdf/case-report.blade.php). |
| Telescope | `^5.20` (dev) | Request/query inspector | Migration: `2026_04_29_103952_create_telescope_entries_table`. |
| Debugbar | `^4.2` (dev) | In-page profiler | |
| Pest | `^4.6` (dev) | Test runner | See [tests/](../../tests/) and [doc 08](08-testing-and-quality-gates.md). |
| Pail | `^1.2.5` (dev) | Tail-style log viewer | Wired into `composer dev`. |
| Pint | `^1.27` (dev) | PHP formatter | |

## Frontend

| Piece | Version | Purpose | Notes |
|---|---|---|---|
| Vite | `^8.0` | Bundler | Config: [vite.config.js](../../vite.config.js). Builds **only** what's imported via Laravel's Vite plugin from [resources/js/app.js](../../resources/js/app.js). Output: `public/build/`. |
| `laravel-vite-plugin` | `^3.0` | Blade `@vite` integration | |
| Tailwind CSS | `^4.0` | Utility CSS | Loaded via `@tailwindcss/vite`. Used selectively; Bootstrap Vuexy is the primary visual system. |
| Bootstrap Vuexy theme | (vendored) | Admin/dashboard skin | Vendor assets and JS in [public/](../../public/) — hand-managed, **not** built by Vite. |
| Alpine.js | (CDN, in layouts) | In-page reactivity | Drives the Add Case wizard's section state. See [doc 04](04-add-case-flow.md) and CLAUDE.md Entry 6. |
| Cropper.js | `^1.6.2` (CDN) | Image crop modal | Loaded in [add-case.blade.php](../../resources/views/content/cases/add-case.blade.php). |

The mirror policy between [public/js/scripts/cases/](../../public/js/scripts/cases/)
and [resources/js/scripts/cases/](../../resources/js/scripts/cases/) is the
single most important frontend rule on this project — see
[doc 05](05-frontend-pipeline.md) and CLAUDE.md Entry 3.

## Database

- **MySQL/MariaDB** locally (`ob1` schema). See the
  `project_local_db_setup` memory note for credentials and seed flow.
- **43 migrations** in [database/migrations/](../../database/migrations/) — see [doc 03](03-database-schema.md).
- Seeders in [database/seeders/](../../database/seeders/), with raw geo
  data in [seeders/sql/sample_data.sql](../../database/seeders/sql/sample_data.sql).
  Use the `seed-geo` skill if PracticesSeeder skips for missing zipcodes.

## External services

| Service | Use | Config |
|---|---|---|
| Google Gemini | Vision + image edit | [config/ai.php](../../config/ai.php), `GEMINI_API_KEY` |
| Ollama (local) | Vision fallback | `AI_OLLAMA_URL`, model env vars |
| Google reCAPTCHA | Login/register/reset bot gate | [VerifyRecaptcha middleware](../../app/Http/Middleware/VerifyRecaptcha.php), [RecaptchaService](../../app/Services/RecaptchaService.php) |
| Local SMTP / Mailpit | Email verification, password reset, doctor approval mails | [app/Mail/](../../app/Mail/) |

## Local toolchain conveniences

- `composer dev` — runs serve + queue + pail + vite concurrently.
- `composer test` — `config:clear` + `artisan test`.
- User-level skills (`~/.claude/skills/`) — `restart-dev-server`,
  `post-pull`, `switch-branch`, `seed-geo`, `pre-commit-checks`,
  `add-case-section-scaffold`, `phase-runner`, `update-architecture-docs`.

## What's intentionally *not* in the stack

- **No SPA framework** (React/Vue) on the user-facing app. Blade + Alpine
  is canonical. Don't introduce one without a design discussion.
- **No queue worker required for normal dev** — mailables run
  synchronously in `local`. The `composer dev` script starts a queue
  listener for completeness, not necessity.
- **No Docker.** Local PHP + MySQL is the supported path.
