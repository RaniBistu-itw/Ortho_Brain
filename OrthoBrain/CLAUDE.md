# OrthoBrain — Conventions and Gotchas

This file captures conventions, patterns, and gotchas earned through bug
discoveries during OB development. Entries are added when a real bug
surfaces a generalizable rule. Each entry names the rule, the reason,
and a reference PR.

## Backend conventions

### Entry 1 — `Auth::id()` vs `currentDoctor()->id`

**Rule:** When scoping queries by case ownership or doctor identity,
never use `Auth::id()` directly. Always go through
`$this->currentDoctor()->id` (or equivalent helper).

**Reason:** `Auth::id()` returns `users.id`. The `cases.doctor_id` column
references `doctors.id`. These are different tables with different IDs.
A user with `users.id = 2` may have `doctors.id = 1`. Using `Auth::id()`
in a `WHERE doctor_id = ?` query silently returns zero rows — endpoints
404, persistence appears broken with no error.

**Reference:** PR #97. See
`CasesController::saveShipping/saveImpressions/saveAdditionalInfo` for
the corrected pattern.

### Entry 2 — Admin and Doctor controller parity

**Rule:** Any persistence work that adds prefill data, serialization, or
read paths to the doctor-side controller must update the admin-side
controller in the same PR.

**Reason:** OB has parallel doctor (`CasesController`) and admin
(`Admin/CasesController`) paths that share data but render through
different controllers. The admin path historically uses Reflection to
reach private methods on the doctor controller (separate technical debt
flagged elsewhere). Adding prefills to one path and not the other creates
a silent asymmetry — admins viewing a case see blank sections that
doctors see populated, with no error.

**Discipline:** When touching `CasesController::edit()`, grep for
`Admin/CasesController` and verify the same data flow. The methods
should be in lockstep.

**Reference:** PR #95. Three prefill sections (additional info, shipping
address, impressions) were added to the doctor edit method but missed on
the admin side; caught only because the audit checked both paths
explicitly.

## Frontend mirror discipline

### Entry 3 — `public/js/` and `resources/js/` mirror discipline

**Rule:** When editing any JavaScript file under
`resources/js/scripts/cases/` or `public/js/scripts/cases/`, update both
copies in the same commit. Never push one without the other.

**Reason:** `public/js/scripts/cases/` is NOT a Vite build output — it's
hand-managed assets loaded directly by Blade templates via `<script>`
tags. Vite outputs to `public/build/` (the standard Laravel + Vite
convention). The two `cases/` directories are independent copies that
must be kept in sync manually.

**Why both exist:** The team is in transition between conventions and
hasn't yet decided whether `public/` or `resources/` should be canonical.
Until that decision is made, keeping them identical is the only safe
default.

**Verification:** After any cases JS change, run:

```
diff -rq resources/js/scripts/cases/ public/js/scripts/cases/
```

Expected output: empty (no differences). If output is non-empty, sync
before pushing.

**Reference:** PR #97. Four files diverged after PR #95 — fixes had been
applied to `public/` only, leaving `resources/` stale.

## Investigation discipline

### Entry 4 — Four-layer audit principle for form-bound data

**Rule:** When auditing or fixing data flow that ends in a form field,
inspect all four layers, not just the JS:

1. **Model and migrations** — what's in the database, what columns,
   what relationships, what types
2. **Controller serialization** — what the server returns to the client
3. **JS state management** — how the client receives and binds data
4. **Template rendering directives** — how the markup actually renders
   the bound data (Blade `@foreach`, Alpine `<template x-for>`, etc.)

**Reason:** Skipping any layer produces audits that are technically
correct but practically incomplete. Bugs hide in the seams between
layers — most often between layers 3 and 4.

**Reference:** PR #98. Originally planned as 2 commits (serializer + JS
hydrate); implementation surfaced 3 additional commits — all discoveries
made by inspecting layer 4 (the Blade template's `<template x-for>`
rendering of country options) rather than relying on static analysis
alone.

### Entry 5 — Field semantics across consumers

**Rule:** When a serializer returns a text field, verify what shape the
consumer (form binding, Alpine state, validation rule) actually expects.
Symmetry with sibling fields is a prior, not a guarantee.

**Reason:** Database fields often have multiple representations: a
human-readable name (`countries.name = "United States"`) and a machine
identifier (`countries.country_code = "US"`). The frontend may bind to
one while the serializer returns the other. Both are strings; neither
throws; the binding silently fails.

**Discipline:** Before assuming a field shape, check:

- The form `<select>` `<option value=...>` — what does it expect?
- The validation rule — what does it validate against?
- Sibling fields in the same form — what shape do *they* hold? (Note:
  this is a prior, not a guarantee. Check directly.)

**Reference:** PR #98 commit `0a0113d`. City and State hold display
names; Country holds an ISO code (`US`, `CA`). Symmetry with city/state
was a false friend. Serializer corrected from `country?->name` to
`country?->country_code`.

## Alpine patterns

### Entry 6 — Alpine `x-model` + `x-for` options race

**Rule:** When assigning to an Alpine state field that's bound via
`x-model` to a `<select>` whose `<option>`s are rendered by
`<template x-for>`, wrap the assignment in `$nextTick`.

**Reason:** During Alpine `init()` and during click handlers, `x-model`
may evaluate the `<select>`'s value before `x-for` has injected the
matching `<option>` into the DOM. With no matching option, the select
falls back to its placeholder and 2-way binds the empty value back to
the state field, clobbering whatever was just assigned.

**Pattern:**

```javascript
this.$nextTick(() => {
  this.country = entry.country;
});
```

For click cascades where downstream side effects (like `syncToState()`)
need the value synchronously, assign synchronously first AND re-affirm
inside `$nextTick`:

```javascript
this.country = entry.country;       // synchronous for syncToState()
this.$nextTick(() => {
  this.country = entry.country;     // re-affirm after Alpine renders
});
```

**Canonical examples:** `prescription.js:83` and `shipping-address.js`
(post-PR #98).

**Reference:** PR #98 commits `69cfaaa` and `15ddf66`. Cost two extra
commits because the pattern wasn't searched for before implementation.
Future fixes touching select options bound via `x-for` should grep for
`nextTick` in `resources/js/` first.

## Local dev environment

### Entry 7 — Stale `php artisan serve` processes

**Rule:** When debugging behavior that should have been fixed by a recent
PR, restart `php artisan serve` before assuming the fix didn't work or
isn't deployed.

**Reason:** `php artisan serve` is a long-running process that loads its
router and bootstrapper at startup. Pulling new commits, switching
branches, or running migrations does NOT cause the running process to
pick up new code. The server continues to serve whatever code was on
disk when it started.

**Symptom pattern:** Behavior that works in one environment (a freshly-
restarted server, a teammate's machine, CI) but fails in another (a
long-lived local dev server). Same DB, same checkout, different running
binary state. Server-side artifacts (logs, DB records, files) are fine,
but page rendering or behavior is wrong.

**Discipline:** After every `git pull` or branch switch, restart any
`php artisan serve` processes:

```
ps aux | grep "artisan serve" | grep -v grep   # see when each was started
lsof -i :8000 | grep LISTEN                    # find PID for a port
kill <PID>
php artisan serve --port=8000                  # restart
```

Multiple servers on different ports compound this — each must be
restarted independently.

**Diagnostic trap to avoid:** When inspecting tile components with
`document.querySelector('.media-tile img')` (or similar multi-match
selectors), `querySelector` returns the FIRST match in DOM order. For
tiles that contain both a placeholder `<img>` and a preview `<img>`
controlled by `x-show`, the first match is usually the placeholder.
A `display: none` on that element means "placeholder hidden because
the tile is filled" — NOT "the real image is hidden". Use
`querySelectorAll` and iterate, or scope to `.media-tile__preview`
specifically.

**Reference:** Issue 3 diagnostic, 2026-05-05. The :8000 server had
been running since 2026-05-04 13:08, predating six merged PRs (#95,
#96, #97, #98, #99, #100). Hours of "case images not appearing"
diagnostic concluded with a server restart fixing the symptom — the
running binary simply hadn't picked up PR #98's hydration fix. The
querySelector trap above masked the fix's effect across two diagnostic
rounds even after the server was restarted.

## Quality gates

### Entry 8 — Database round-trip discipline

**Rule:** Every change that adds or modifies a database column must
explicitly verify the full round trip: write path, all read paths,
serialization, and prefill hydration. (See Entry 4 for the same
four-layer pattern viewed from form-field down — this entry covers
the column-up direction with the multi-read-path and null-handling
clauses below.)

**Reason:** Adding a column without auditing all read paths produces
silent gaps. The column may be written correctly but invisible in
the case list, the PDF, the admin view, or the activity log. Each
read path is a separate consumer with its own potential to break or
omit the new field.

**Discipline:** For every column-touching PR, audit at minimum:
- Migration adds the column
- Model includes it in `$fillable` and any `$casts`
- Write paths persist it (controller `update()` calls, service
  layer methods)
- All read paths consume it: edit endpoint serialization, list
  endpoint, admin parity endpoint (Entry 2), PDF template, any export
- Frontend hydration reads it from prefill (with appropriate default
  for null/empty)
- Existing rows handle null gracefully (no crashes, no literal
  "null" displayed in UI)

If any read path is intentionally excluded from the new column (e.g.,
"this is internal data, list view doesn't need it"), document the
reason in the PR description so future readers know it was a choice
not an oversight.

**Reference:** PR #103 (submitter initials). The case list read-path
audit was raised mid-PR and added to scope before merge.

### Entry 9 — Manual smoke test discipline

**Rule:** Every PR that touches user-facing flows ships with a
manual smoke test documented in the PR description, executed by the
human author before ready-for-merge.

**Reason:** OB has feature-level Pest tests but no automated
end-to-end smoke tests. Pest verifies endpoints; it doesn't verify
the user can navigate the full flow. Bugs that pass unit tests but
break the real flow (Alpine init order, prefill hydration timing,
form submission cascades) only surface in a real browser.

**Discipline:** PR description must include a smoke test section:

```
## Smoke test
- [x] Login as [role]
- [x] Navigate to [feature entry point]
- [x] Perform [primary action]
- [x] Verify [primary expected outcome]
- [x] Reload page → verify state persists
- [x] [Other parity / regression checks specific to PR]
```

Each ticked box means the human author manually executed and
confirmed the step. Unchecked boxes block merge.

Future: Automated smoke tests (Laravel Dusk or equivalent) are
backlog-tracked. Until they exist, manual smoke tests are the gate.

**Reference:** PR #103. Smoke test discipline was added explicitly
to PR scope mid-implementation.

### Entry 10 — Validation feedback consistency

**Rule:** For every new validated field, document and verify the
user-facing feedback at each rejection mode.

**Reason:** Backend 422 responses are necessary but not sufficient.
The user needs to see why their input was rejected, where on the page
the error appears, and what to do next. Different validation sources
produce different feedback shapes:

- Client-side regex / pattern attribute → browser-native message
- Client-side Alpine validation → inline error display
- Server-side 422 → catch handler surfaces via toast / alert
- HTML `maxlength` attribute → silently caps input (no error needed)

These four sources coexist for any single field. The PR must verify
user-facing outcome at each rejection mode, not just that the backend
rejects correctly.

**Discipline:** For each validation rule on a new field, document in
the PR description:
- The rejection mode (e.g., "empty field on submit")
- Where validation fires (client / server / both)
- What the user sees (specific message text, location on page)
- Whether focus moves to the offending field

If client and server validations could disagree (different regex,
different length limits, different format expectations), reconcile
before merging. Entry 5 (field semantics across consumers) applies —
symmetry matters across all four validation sources.

**Reference:** PR #103. Initial scope had server `max:10` paired
with client `maxlength:5` — a silent contract mismatch caught in
pre-flight reading. The reconciliation became this entry's example.
