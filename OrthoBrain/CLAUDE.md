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
