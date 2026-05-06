> _Last verified: against origin/dev @ `096aea5` on 2026-05-06. If editing, update this stamp._

# 05 — Frontend Pipeline

OrthoBrain's frontend is **two layers** that look similar but are wired
differently. Confusing them causes the most common "I edited the file
but nothing changed" bug.

## The two layers

### Layer A — Vite-built (canonical Laravel)

- Source: [resources/js/app.js](../../resources/js/app.js), [resources/css/app.css](../../resources/css/app.css)
- Bundler: [vite.config.js](../../vite.config.js) + `laravel-vite-plugin`
- Output: `public/build/` (manifest.json + hashed assets)
- Loaded in Blade via `@vite([...])` in layouts: [layouts/app.blade.php](../../resources/views/layouts/app.blade.php), [layouts/admin.blade.php](../../resources/views/layouts/admin.blade.php)
- Tailwind 4 lives here (`@tailwindcss/vite`)

### Layer B — Hand-managed in `public/`

- Files under [public/css/](../../public/css/), [public/js/](../../public/js/), [public/images/](../../public/images/), [public/vuexy/](../../public/vuexy/), [public/models/](../../public/models/) — *not* Vite output
- Loaded directly via `<link>` and `<script>` tags in Blade with `asset()` and a cache-buster
- Origin: imported from the Bootstrap Vuexy admin theme; the team kept this convention to avoid rewriting the theme

This dual setup is intentional — see the `project_stack` memory note:
"public/-as-source-of-truth + manual mirror to resources/".

## The mirror rule (CLAUDE.md Entry 3)

These two folders **must stay byte-identical**:

```
resources/js/scripts/cases/   ↔   public/js/scripts/cases/
```

- [public/js/scripts/cases/](../../public/js/scripts/cases/) is the one actually loaded by Blade `<script>` tags
- [resources/js/scripts/cases/](../../resources/js/scripts/cases/) is preserved for the eventual Vite-canonical migration

Verify before pushing:

```bash
diff -rq resources/js/scripts/cases/ public/js/scripts/cases/
# expected: no output
```

The team has not yet decided which side becomes canonical. Until they
do, **edit both in the same commit**. Never push one without the other.

## Cache-busting rule (memory: feedback_cache_bust_public_assets)

Any edit to a `public/` asset that's referenced from a Blade template
**must** ship with `?v={filemtime}` on the `<link>` / `<script>`:

```blade
<link rel="stylesheet"
      href="{{ asset('css/base/pages/add-case.css') }}?v={{ @filemtime(public_path('css/base/pages/add-case.css')) ?: time() }}">
```

Without this, browsers serve stale assets and your fix ships invisibly.
Bit us in PR #61 → PR #62.

## Vendor JS

CDN-loaded in layouts and partials, not bundled:
- Cropper.js `1.6.2` — image crop modal
- Bootstrap Vuexy vendor JS — under `public/vuexy/`
- (Per-page CDNs as needed — see each Blade head)

If you add a CDN dependency, prefer a fixed version over `latest`.

## CASE_API_BASE switch

[case-api.js](../../resources/js/scripts/cases/case-api.js) reads a global
`window.CASE_API_BASE` set by [add-case.blade.php](../../resources/views/content/cases/add-case.blade.php) based on `$adminMode`:

- Doctor view → `/dev/cases`
- Admin view → `/admin/cases`

This is what lets the same JS run under both controllers without
branching at every fetch site. See the `feedback_team_wiring_patterns`
memory note for related conventions.

## SCSS / CSS

- Page-level CSS (e.g. `add-case.css`) lives under [public/css/](../../public/css/) and is loaded by `<link>` (cache-busted)
- Tailwind 4 utilities are available app-wide via the Vite-built bundle
- The teal `$primary` is the Vuexy theme variable — see project_stack memory

## Alpine

Loaded via the Vuexy vendor bundle in [layouts/app.blade.php](../../resources/views/layouts/app.blade.php). All Add Case wiring is Alpine. Patterns to remember:

- `$nextTick` around `<select>` assignments when options come from `<template x-for>` — CLAUDE.md Entry 6
- `@click.stop` on triggers that programmatically click hidden inputs — memory: feedback_synthetic_click_bubble

## Don't do this

- ❌ Edit `public/js/scripts/cases/foo.js` only and ship — push gets the
  doctor view but `resources/js/` is now stale; next person who touches
  the file via the wrong copy will revert your change.
- ❌ Move a file from `public/` into `resources/` "to clean up" without
  team alignment — breaks Blade `<script>` tags wired to `asset()`.
- ❌ Skip the cache-buster — your CSS change won't appear in the
  browser, you'll think the change failed.
- ❌ Add a build step for `public/js/scripts/` — that's the manual layer
  by design.

## Asset paths in dompdf (PDF export)

For the PDF in [pdf/case-report.blade.php](../../resources/views/content/cases/pdf/case-report.blade.php), use `public_path('img/...')` not `asset(...)`. dompdf doesn't fetch over HTTPS reliably and breaks silently on the wrong path form.

## Build commands

```bash
npm run dev    # Vite dev server (HMR for resources/ side)
npm run build  # Vite production build → public/build/
```

`composer dev` runs `npm run dev` alongside the PHP server, queue, and
log tail. See [Docs/dev-commands.md](../dev-commands.md).
