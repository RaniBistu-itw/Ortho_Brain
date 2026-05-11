> _Last verified: against origin/dev @ `096aea5` on 2026-05-06. If editing, update this stamp._

# 08 — Testing & Quality Gates

OrthoBrain's quality gates are a mix of automated (Pest) and manual
(documented smoke tests, validation-feedback discipline, the
`pre-commit-checks` skill). This page maps what runs where; the *rules*
live in CLAUDE.md Entries 8–10.

## Automated tests — Pest

Layout: [tests/](../../tests/)

```
tests/
├── Pest.php               # global config / setup
├── TestCase.php           # base test case
├── Unit/
│   ├── ExampleTest.php
│   └── AI/
│       ├── VisionServiceTest.php
│       ├── ImageEditServiceTest.php
│       ├── CannedFallbackProviderTest.php
│       └── PromptBuilderTest.php
└── Feature/
    ├── ExampleTest.php
    ├── Cases/
    │   ├── SaveDraftTest.php
    │   ├── SubmitCaseTest.php
    │   ├── AdminStatusTransitionTest.php
    │   └── ShippingPrefillTest.php
    └── Admin/
        ├── ProductSubcategoryTest.php
        ├── ProductTest.php
        ├── ProductCategoryTest.php
        ├── CountryTest.php
        ├── ZipcodeTest.php
        ├── ScannerTest.php
        ├── StateTest.php
        └── CityTest.php
```

Run:
```bash
composer test                # config:clear + artisan test
php artisan test --filter=ShippingPrefill   # single file
./vendor/bin/pest             # direct
```

Coverage shape:
- **Cases**: draft persistence, submission, admin status transitions, prefill round-trips
- **Admin masters**: standard CRUD + AJAX endpoints
- **AI**: provider chain ordering, demo-case gating, prompt construction (no live provider hits)
- **Auth, profile, registration**: not yet covered → backlog

What's **not** covered automatically:
- Browser-level interaction (Alpine init order, click cascades, Cropper.js)
- Multi-step wizard navigation
- Visual regression
- AI live calls

These are the gap that manual smoke tests fill.

## Quality gates (CLAUDE.md Entries 8–10)

### Entry 8 — Database round-trip discipline
Every column-touching PR audits: migration, model `$fillable`/`$casts`,
write paths, **all** read paths (edit, list, admin parity, PDF,
exports), frontend prefill (with null handling). Intentional exclusions
must be documented in the PR description.

### Entry 9 — Manual smoke test discipline
Every PR touching user-facing flows ships with a manual smoke test
checklist in the PR description, executed by the human author before
ready-for-merge. Template:

```markdown
## Smoke test
- [x] Login as [role]
- [x] Navigate to [feature entry point]
- [x] Perform [primary action]
- [x] Verify [primary expected outcome]
- [x] Reload page → verify state persists
- [x] [Other parity / regression checks specific to PR]
```

Future: Laravel Dusk (or equivalent) for automated browser smoke tests
is backlog. Until it lands, this is the gate.

### Entry 10 — Validation feedback consistency
For each new validated field, document the user-facing outcome at each
rejection mode:
- Client-side regex / `pattern` attribute → browser-native message
- Client-side Alpine validation → inline error
- Server-side 422 → toast / alert via catch handler
- HTML `maxlength` → silent input cap (no error needed)

Reconcile divergences before merging (e.g., server `max:10` paired
with client `maxlength:5` is a contract mismatch — Entry 10's
canonical example).

## The `pre-commit-checks` skill

User-level skill at `~/.claude/skills/pre-commit-checks/`. Trigger:
"ready to commit", "pre-commit", "is this safe to commit". Runs:

1. Laravel cache clear
2. Frontend build (`npm run build`)
3. Smoke HTTP test against `/login`
4. Pint (if available)
5. Pest (if available)

Surfaces failures before they reach the commit. Doesn't replace manual
smoke testing; it catches the obvious "did I break the build" class
of problems.

## Linting & formatting

- **PHP**: [Pint](https://laravel.com/docs/pint) (`./vendor/bin/pint`)
- **JS**: no project linter wired today
- **Editor**: [.editorconfig](../../.editorconfig) at repo root

## Pre-merge checklist (composite)

A PR is ready to merge when:

1. ✅ Pest passes (`composer test`)
2. ✅ Pint passes (`./vendor/bin/pint --test`)
3. ✅ Frontend builds (`npm run build`)
4. ✅ Manual smoke test in PR description, all boxes ticked (Entry 9)
5. ✅ Validation feedback documented per rejection mode (Entry 10, if applicable)
6. ✅ `public/js/scripts/cases/` and `resources/js/scripts/cases/` are byte-identical (CLAUDE.md Entry 3) — `diff -rq` returns clean
7. ✅ Admin↔doctor parity audited if persistence/prefill changed (CLAUDE.md Entry 2, [doc 06](06-admin-parity.md))
8. ✅ Cache-busters present on any changed `public/css/`, `public/js/` Blade references (memory: feedback_cache_bust_public_assets)

## Local environment health

The most expensive class of debugging false-leads on this project is
the *stale `php artisan serve`* trap (CLAUDE.md Entry 7). Before
concluding a fix doesn't work:

```bash
ps aux | grep "artisan serve" | grep -v grep   # see uptime
lsof -i :8000 | grep LISTEN                    # find PID
```

If the server's been running since before the PR you're testing was
merged, restart it. The `restart-dev-server` user-level skill does this
in one shot.

## Telescope + Debugbar

Both wired locally (dev-only):
- Telescope: `/telescope` — request inspector, query log, exception list
- Debugbar: in-page profiler

Don't enable either in production. They're declared in `require-dev` so
`composer install --no-dev` excludes them.
