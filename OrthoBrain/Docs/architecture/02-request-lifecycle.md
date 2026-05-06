> _Last verified: against origin/dev @ `096aea5` on 2026-05-06. If editing, update this stamp._

# 02 — Request Lifecycle

How a browser request flows through OrthoBrain. Use this when adding a
new route, changing auth, or debugging a 404/redirect.

## Entry point

[bootstrap/app.php](../../bootstrap/app.php) wires:

- Routing → [routes/web.php](../../routes/web.php) (single file, ~342 lines)
- Middleware aliases (`admin`, `active.practice`, `recaptcha`)
- Global web middleware appends ([SecurityHeaders](../../app/Http/Middleware/SecurityHeaders.php), [LogSlowRequests](../../app/Http/Middleware/LogSlowRequests.php))
- Two `render()` callbacks for exception handling (see *Exception rendering* below)

## Three route groups

`routes/web.php` is organised into three top-level groups:

### 1. Public / shared auth (no prefix, no auth middleware)
- `GET /`, `/login`, `/register`, `/verify-email/*`, `/forgot-password`, `/reset-password/{token}`
- `GET /contact-support`, `POST /contact-support`, `GET /contact-support/thanks`
- `GET /practice-search` — public autocomplete on the register page
- `POST` endpoints throttled with named limiters (`register`, `verify-otp`, `password-reset`, `resend-verification`) — see *Rate limiters* below
- `recaptcha:<flow>` middleware on login/register/reset-password

### 2. Doctor area — `prefix('dev')->name('doctor.')`, middleware `['web', 'auth']`
- `/dev/dashboard`, `/dev/cases/*`, `/dev/notifications/*`, `/dev/profile/*`
- Cases + AI endpoints additionally require `active.practice` middleware
- Patient autocomplete, ZIP search, case media upload/destroy
- `case media destroy` uses **POST** not DELETE — PHP 8.3 `request_parse_body()` fatal on DELETE+JSON. See route comment + memory `project_php_version_gotcha`.

### 3. Admin area — `prefix('admin')->name('admin.')`, middleware `['web', 'admin']`
- Admin dashboard, doctor approval, practice management, master data CRUD (countries/states/cities/zipcodes/products/scanners)
- AJAX subprefix `admin/ajax/*` for typeahead lookups ([Admin/Ajax/LookupController](../../app/Http/Controllers/Admin/Ajax/LookupController.php))
- Admin Cases (full visibility) — see [doc 06](06-admin-parity.md) on the doctor↔admin parity rule

## Identity model: `users` ↔ `doctors` / `admins`

This is the most-cited gotcha (CLAUDE.md Entry 1). Two parallel role
tables share `users` for auth:

```
users (id, email, password, role, is_active, ...)
  ├── doctors (id, user_id, practice_id, ...)
  └── admins  (id, user_id, ...)
```

`Auth::id()` returns `users.id`. `cases.doctor_id` references
`doctors.id` — **different IDs**. Always go through:

```php
$this->currentDoctor()->id      // in CasesController
currentPractice()->id           // global helper, app/Support/helpers.php
```

`currentPractice()` reads from [app/Support/ActivePractice.php](../../app/Support/) (session-scoped active practice context).

## Middleware chain

| Alias | Class | What it gates |
|---|---|---|
| `web` | Laravel default | Sessions, CSRF, cookies |
| `auth` | Laravel default | Authenticated user (any role) |
| `admin` | [EnsureSuperAdmin](../../app/Http/Middleware/EnsureSuperAdmin.php) | `users.role === 'ADMIN'` AND `is_active`. Logs out + redirects on mismatch. |
| `active.practice` | [EnsureActivePractice](../../app/Http/Middleware/EnsureActivePractice.php) | Doctor has at least one APPROVED + ACTIVE practice. Otherwise → `doctor.practices.pending` (with a small allowlist of routes that bypass: profile, switch, request, leave, logout). |
| `recaptcha:<flow>` | [VerifyRecaptcha](../../app/Http/Middleware/VerifyRecaptcha.php) | Google reCAPTCHA verification on login/register/reset |
| `throttle:<name>` | Laravel | Named rate limiters (see below) |

Globally appended on every web request:
- [SecurityHeaders](../../app/Http/Middleware/SecurityHeaders.php) — CSP, X-Frame-Options, etc.
- [LogSlowRequests](../../app/Http/Middleware/LogSlowRequests.php) — log requests over a threshold

## Rate limiters

Configured in [AppServiceProvider::configureRateLimiters()](../../app/Providers/AppServiceProvider.php):

| Name | Limit | Key | Why |
|---|---|---|---|
| `register` | 5/min | IP | Slow bot signups |
| `password-reset` | 3/hour | IP | Prevent reset-link mail-bombing + enumeration |
| `verify-otp` | 5 / 15 min | email (lowercased) | OTP brute-force protection (paired with 30-min OTP TTL) |
| `resend-verification` | 3/hour | email | Prevent resend mail-bombing |

Login does **not** use a named limiter — it has a per-account lockout
in `users.failed_login_attempts` / `locked_until` instead.

Per-route `throttle:N,M` limits (Laravel default keyed by user/IP):
- `cases.media.upload` — 60/1
- `cases.photos.classify` — 30/60
- `cases.smile-plan.generate` — 10/60
- `cases.smile-preview.generate` — 5/1

## Exception rendering

Two `render()` callbacks in [bootstrap/app.php](../../bootstrap/app.php), in registration order:

1. **`ThrottleRequestsException` graceful fallback** — for browser
   submissions, redirects back with input + flashes
   `throttle_retry_at` so login/register pages can render a live
   countdown. JSON/AJAX clients get the default 429.
2. **PHP 8.3 dev-only debug page** — bypasses Symfony's HtmlErrorRenderer
   (which fatals on PHP 8.3 because var-dumper v8 calls a method only
   in PHP 8.4+). Only active when `PHP_VERSION_ID < 80400`,
   `APP_DEBUG=true`, and the request expects HTML. Skips
   AuthenticationException, ValidationException,
   TokenMismatchException, HttpResponseException so those keep their
   normal redirect behaviour.

## Service providers

[config/app.php](../../config/app.php) registers:

- [AppServiceProvider](../../app/Providers/AppServiceProvider.php) — Bootstrap pagination, `Doctor::observe(DoctorObserver::class)`, HTTPS forcing when `APP_URL` is https, rate limiters
- [AiServiceProvider](../../app/Providers/AiServiceProvider.php) — Singletons `VisionService` and `ImageEditService` from `config/ai.php` provider chains. See [doc 07](07-ai-services.md).
- [TelescopeServiceProvider](../../app/Providers/TelescopeServiceProvider.php) — Local-only

## Where to look for X

- New endpoint contract → [Docs/api.md](../api.md)
- New route → [routes/web.php](../../routes/web.php)
- New middleware → register alias in [bootstrap/app.php](../../bootstrap/app.php)
- New rate limiter → [AppServiceProvider::configureRateLimiters](../../app/Providers/AppServiceProvider.php)
- `Auth::id()` smell → CLAUDE.md Entry 1
