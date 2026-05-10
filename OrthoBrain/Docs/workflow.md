# OrthoBrain — New Developer Workflow Guide

A concise orientation to how the application is structured, who uses it,
and how the major flows work. Read this alongside `CLAUDE.md` (conventions
and gotchas) and `Docs/architecture/00-overview.md` (deeper architecture
index) before touching any code.

---

## 1. What Is OrthoBrain?

OrthoBrain is a case-management platform for orthodontic practices. Doctors
submit treatment cases through a multi-section wizard; admins review and
approve those cases, then coordinate production and shipping.

---

## 2. Tech Stack

| Layer | Technology | Notes |
|---|---|---|
| Language | PHP 8.3 + | Laravel 13 framework |
| Frontend framework | Alpine.js | CDN-loaded; drives all in-page reactivity |
| CSS / UI | Bootstrap Vuexy | Hand-managed in `public/`; not rebuilt by Vite |
| Build tool | Vite 8 | Outputs to `public/build/`; covers app-specific JS/CSS only |
| PDF export | dompdf 3.1 | Server-rendered case report |
| Database | MySQL / MariaDB | 44 migrations organised by domain |
| AI vision | Google Gemini | Photo QC, smile plan; falls back to Ollama → Canned |
| Email | SMTP / Mailpit | Verification, approval/rejection, password reset |
| Testing | Pest 4.6 | Feature + unit tests; no automated E2E yet |
| Dev runner | `composer dev` | Starts serve + queue + pail + Vite concurrently |

---

## 3. User Roles

| Role | Entry point | Middleware | Primary responsibilities |
|---|---|---|---|
| **Doctor** | `/dev/*` | `auth` | Create cases, upload media, track status, manage profile |
| **Admin** | `/admin/*` | `admin` | Review cases, approve/reject doctors, manage master data |
| *(Public)* | `/login`, `/register` | — | Authentication and registration only |

Doctors and admins share a single `users` table with a `role` enum.
`users.id` ≠ `doctors.id` — always resolve via `currentDoctor()->id`
(see CLAUDE.md Entry 1).

---

## 4. Doctor Workflow

### 4a. Onboarding sequence

| Step | URL | What happens |
|---|---|---|
| 1. Register | `GET /register` | Email + password + practice selection |
| 2. Verify email | `GET /verify-email` | OTP sent; must be confirmed before login |
| 3. Admin approves | Admin dashboard | Admin approves doctor; approval email sent |
| 4. Join practice | `/dev/practices` | Doctor joins primary practice (required for dashboard) |
| 5. Dashboard | `/dev/dashboard` | Main entry point; visible only with active practice |

### 4b. Case creation wizard (10 sections)

| # | Section | Key data | Saved at |
|---|---|---|---|
| 1 | Patient information | Name, DOB, chart ID, phone | `POST /dev/cases/{case}/patient` |
| 2 | Prescription | Arch, IPR, attachments, elastics, extractions | `POST /dev/cases/{case}/prescription` |
| 3 | Additional information | Checklist (JSON) | `POST /dev/cases/{case}/additional` |
| 4 | Impressions | Scanner type, impression method | `POST /dev/cases/{case}/impressions` |
| 5 | Photographs | Up to 9 tiles, drag-reorder | `POST /media/upload`, `POST /media/reorder` |
| 6 | X-Rays | Tile-based upload, drag-reorder | Same media endpoints |
| 7 | Shipping address | To / from, geo lookup | `POST /dev/cases/{case}/shipping` |
| 8 | Submit order | Review + confirm | `POST /dev/cases/{case}/submit` |

Each section saves independently. The final submit only transitions the
case status from DRAFT → SUBMITTED; it does not re-save section data.

### 4c. Case states

| Status | Who sets it | Doctor can edit? | Admin can edit? |
|---|---|---|---|
| `DRAFT` | Created automatically | Yes | No |
| `SUBMITTED` | Doctor (submit step) | No | Yes |
| `IN_REVIEW` | Admin | No | Yes |
| `APPROVED` | Admin | No | No |
| `REJECTED` | Admin | No | No |

### 4d. Media upload rules

| Scenario | Correct action |
|---|---|
| New file from disk | Open picker → crop modal → upload blob |
| Drag-reorder, both tiles have blobs | Destroy + upload (swap) |
| Drag-reorder, one or both tiles are URL-only (prefill) | Call `POST /media/reorder` (server-side tile_id swap) |
| Swap when URL-only tile exists | Fetch URL → blob first, then swap |

Never destroy + re-upload a URL-only tile — silent data loss (CLAUDE.md Entry 11).

---

## 5. Admin Workflow

### 5a. Doctor management

| Action | Trigger | Outcome |
|---|---|---|
| Approve | Registration review | Status → approved; email sent |
| Reject | Registration review | Status → rejected; email sent with optional reason |
| Suspend | Active doctor | Login blocked; session invalidated |
| Reactivate | Suspended doctor | Login restored |

### 5b. Case review steps

| Step | URL | Notes |
|---|---|---|
| View submitted cases | `GET /admin/cases` | Filters by status, doctor, practice |
| Open case | `GET /admin/cases/{case}/edit` | Full prefill via same wizard template (`$adminMode = true`) |
| Move to in-review | Status action | SUBMITTED → IN_REVIEW |
| Approve | Status action | IN_REVIEW → APPROVED |
| Reject | Status action + reason | IN_REVIEW → REJECTED |

Patient first/last name and DOB are locked — admin cannot edit them.

### 5c. Master data managed by admin

| Domain | Controller | Notes |
|---|---|---|
| Products | `Admin/ProductController` | Categories, subcategories, images |
| Scanners | `Admin/ScannerController` | Used in impressions section |
| Geographic | `Admin/Country/State/City/ZipcodeController` | Cascading AJAX dropdowns |
| Practices | `Admin/PracticeController` | Edit info; bulk-approve pending members |

---

## 6. Key Directory Map

| Path | What lives here |
|---|---|
| `app/Http/Controllers/` | Doctor-side controllers |
| `app/Http/Controllers/Admin/` | Admin-side controllers (parity with doctor) |
| `app/Http/Controllers/AI/` | Image analysis, smile preview |
| `app/Http/Controllers/Auth/` | Login, register, OTP verification, password reset |
| `app/Models/` | Eloquent models |
| `resources/views/content/cases/` | Case wizard Blade templates + section partials |
| `resources/views/admin/` | Admin views |
| `resources/js/scripts/cases/` | Alpine-driven JS for case wizard (must mirror `public/`) |
| `public/js/scripts/cases/` | Live copies loaded by Blade `<script>` tags |
| `public/build/` | Vite build output (do not hand-edit) |
| `routes/web.php` | All HTTP routes (`/dev/*` + `/admin/*` + public) |
| `database/migrations/` | 44 migrations organised by domain |
| `Docs/architecture/` | Deep-dive architecture docs (01–09) |
| `CLAUDE.md` | Conventions and gotchas — **read first** |

---

## 7. Request Lifecycle (summary)

```
Browser request
  └─ routes/web.php  (role-gated middleware)
       └─ Controller
            ├─ Form Request (validation)
            ├─ Service / Model query  ← always scope by currentDoctor()->id
            └─ View / JSON response
                  └─ Blade template
                       └─ Alpine.js (in-page state)
                            └─ Vite assets (public/build/) +
                               Hand-managed assets (public/js/)
```

---

## 8. AI Services

| Provider | Role | Trigger |
|---|---|---|
| Google Gemini | Photo QC + smile plan generation | `POST /photos/classify`, `POST /smile-plan/generate` |
| Ollama (local) | Vision fallback if Gemini fails | Automatic |
| Canned (mock) | Fallback if Ollama unavailable | Automatic; configured in `config/ai.php` |
| Smile Preview | Before/after image generation | `POST /smile-preview/generate` (rate-limited 5/min) |

---

## 9. Critical Conventions (quick reference)

Full explanations with reasoning live in `CLAUDE.md`. This table is a
quick-scan only — do not substitute it for reading the source.

| # | Rule | Where it bites |
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
| 11 | Never destroy + re-upload a URL-only media tile | Media swap / drag-reorder |

---

## 10. Development Setup (quick start)

| Step | Command |
|---|---|
| Install PHP deps | `composer install` |
| Install JS deps | `npm install` |
| Copy env | `cp .env.example .env && php artisan key:generate` |
| Run migrations | `php artisan migrate` |
| Seed geo data | `php artisan db:seed --class=GeoSeeder` (see `Docs/dev-commands.md`) |
| Start dev stack | `composer dev` (serve + queue + pail + Vite) |
| Run tests | `composer test` |

After any `git pull` or branch switch, restart the dev server — stale
`php artisan serve` processes do not pick up new code (CLAUDE.md Entry 7).

---

## 11. Where to Go Next

| Question | Resource |
|---|---|
| How does the case wizard work end-to-end? | `Docs/architecture/04-add-case-flow.md` |
| What's the full database schema? | `Docs/architecture/03-database-schema.md` |
| How does the frontend pipeline work? | `Docs/architecture/05-frontend-pipeline.md` |
| How are doctor and admin controllers kept in sync? | `Docs/architecture/06-admin-parity.md` |
| How does AI classification work? | `Docs/architecture/07-ai-services.md` |
| What tests exist and how do I run them? | `Docs/architecture/08-testing-and-quality-gates.md` |
| What are all the gotchas I need to know? | `CLAUDE.md` |
