> _Last verified: against feat/b2-admin-parity-reload @ `e3ea0a4` on 2026-05-11. If editing, update this stamp._

# 06 — Admin / Doctor Parity

OrthoBrain has **two parallel controllers** for the same data: the
doctor's view and the admin's view. They share data and views but
render through different controllers and routes. Keeping them in
lockstep is a discipline, not an automatism.

This page is the system-map view; the *rule* lives in CLAUDE.md
Entry 2 and the four-layer audit pattern in Entry 4.

## The two paths

| Aspect | Doctor | Admin |
|---|---|---|
| Route prefix | `/dev` | `/admin` |
| Middleware | `web, auth` (+ `active.practice` on cases/AI) | `web, admin` |
| Cases controller | [CasesController](../../app/Http/Controllers/CasesController.php) | [Admin/CasesController](../../app/Http/Controllers/Admin/CasesController.php) |
| Layout | [layouts/app.blade.php](../../resources/views/layouts/app.blade.php) | [layouts/admin.blade.php](../../resources/views/layouts/admin.blade.php) |
| Add Case view | [add-case.blade.php](../../resources/views/content/cases/add-case.blade.php) (`$adminMode = false`) | same template (`$adminMode = true`) |
| Case list | [case-list.blade.php](../../resources/views/content/cases/case-list.blade.php) | [admin/cases/](../../resources/views/admin/cases/) |
| Status mutation | n/a (only `submit`) | `POST /admin/cases/{case}/status` |
| Identity scoping | `doctor_id = currentDoctor()->id` AND `practice_id = currentPractice()->id` | All cases (no scope) |

## Shared blade with `$adminMode`

[add-case.blade.php](../../resources/views/content/cases/add-case.blade.php) renders for both. Branch points:

```php
$adminMode = $adminMode ?? false;
$apiBase   = $adminMode ? '/admin/cases' : '/dev/cases';
$backUrl   = $adminMode ? route('admin.cases.index') : route('doctor.cases.index');
@extends($adminMode ? 'layouts.admin' : 'layouts.app')
```

Section visibility filtering uses the `adminOnly` flag — currently
`Perfect Smile Plan` is admin-only:

```php
$sections = array_values(array_filter($sections, fn ($s) => $adminMode || empty($s['adminOnly'])));
```

`window.CASE_API_BASE` is set from `$apiBase` so JS in
[case-api.js](../../resources/js/scripts/cases/case-api.js) routes its
fetches to the right controller. See [doc 05](05-frontend-pipeline.md).

## Reflection debt (technical debt, called out)

The admin controller historically reaches into the doctor controller's
**private** methods via PHP Reflection to share serialization /
prefill logic. This is an explicit known smell — fragile, and a refactor
target. Until refactored:

- Treat the doctor controller's `protected`/`private` helper methods as
  part of the contract that admin depends on.
- Renaming or removing one of these methods without grepping
  `Admin/CasesController` is a foot-gun.

This is logged for future cleanup — not for ad-hoc fixes.

## Audit checklist (CLAUDE.md Entry 2)

When you touch persistence, prefill, or read paths in
`CasesController`, run the checklist:

```bash
# 1. What admin controller methods exist?
grep -nE 'function (edit|index|show|update|store|destroy)' \
  app/Http/Controllers/Admin/CasesController.php

# 2. Find Reflection access points
grep -nE 'Reflection|->setAccessible|invokeArgs' \
  app/Http/Controllers/Admin/CasesController.php

# 3. Diff what each side passes to the view
grep -nE 'compact\(|view\(.+\[|->with\(' \
  app/Http/Controllers/CasesController.php \
  app/Http/Controllers/Admin/CasesController.php
```

If you added a prefill key on the doctor side, the admin side must pass
it to the view too — or the shared blade gets `null` and silently
renders blank. PR #95 was the discovery PR; PR #97 fixed the shipping/
impressions/additional-info parity.

## Status transitions

Doctor can only `POST /dev/cases/{case}/submit` (DRAFT → SUBMITTED).

Admin owns the rest of the lifecycle:
```
SUBMITTED → IN_REVIEW → APPROVED | REJECTED
```
via `POST /admin/cases/{case}/status` ([Admin/CasesController::updateStatus](../../app/Http/Controllers/Admin/CasesController.php)).

Allowed-status / actor-status mismatch checks live in the admin
controller. Test coverage:
[tests/Feature/Cases/AdminStatusTransitionTest.php](../../tests/Feature/Cases/AdminStatusTransitionTest.php).

## Write-side parity — Sprint B-2 (shipped 2026-05-11)

Admin has full write parity for case data. Implemented in PR #129 + media guard fix.

### What shipped

**5 new admin section-save routes** (all in `Admin\CasesController`):

| Route | Method | Notes |
|---|---|---|
| `POST /admin/cases/{id}/shipping` | `saveShipping()` | Same payload as doctor; no draft guard |
| `POST /admin/cases/{id}/impressions` | `saveImpressions()` | Same payload as doctor; no draft guard |
| `POST /admin/cases/{id}/additional` | `saveAdditionalInfo()` | Reuses `AdditionalInformationRequest`; no draft guard |
| `POST /admin/cases/{id}/patient` | `savePatient()` | Non-identity fields only (see locked fields below) |
| `POST /admin/cases/{id}/submit-order` | `saveSubmitOrder()` | Initials save; no draft guard |

`POST /admin/cases/{id}/prescription` was already live via `PrescriptionController` (which has its own `role === 'ADMIN'` branch, no draft guard for admin).

**Admin media routes** (PR #128 + B-2 guard fix):

`Admin\CaseMediaController` extends the base `CaseMediaController`, overrides `resolveCaseForDoctor()` (no doctor scope) and `abortIfNotDraft()` (no-op — admin can edit media on any non-DRAFT case).

### Read-only enforcement (window.__isReadOnly)

The formula in `add-case.blade.php` branches by role:

```js
window.__isReadOnly = window.CASE_ADMIN_MODE
  ? (window.__caseStatus === 'APPROVED' || window.__caseStatus === 'REJECTED')
  : (window.__caseStatus !== 'DRAFT');
```

- **Doctor:** read-only on any non-DRAFT status.
- **Admin:** read-only only on APPROVED/REJECTED. To edit an APPROVED case, admin transitions it to IN_REVIEW first — the page reloads and the form becomes editable.
- **Important:** the server-side section-save endpoints have NO read-only guard for APPROVED/REJECTED. Client-side enforcement is the only layer. A crafted request could bypass it.

### Patient identity locked fields

`patient.first_name`, `patient.last_name`, `patient.date_of_birth` are never editable by admin.

Enforced at **two layers**:
1. **Client-side:** `patient-information.js` `init()` imperatively sets `el.disabled = true` for `pi-first-name`, `pi-last-name`, `pi-dob` when `window.CASE_ADMIN_MODE = true`, regardless of case status.
2. **Server-side:** `Admin\CasesController::savePatient()` simply does not include those three fields in the `update()` call — even if sent in the request body they are silently ignored (not a 422).

All other patient fields (`biological_gender`, `biological_gender_other`, `chart_id`, `chief_complaint`, `email`, `phone`) are editable by admin on SUBMITTED/IN_REVIEW cases.

### Admin acts on doctor's behalf

Case's `doctor_id`, `practice_id`, `submitted_at`, and `submitter_initials` are preserved on admin edits. Admin identity captured in audit context (when audit log lands).

### Status change UX

After any `POST /admin/cases/{id}/status` success, the page shows a brief toast (`MediaTileHelpers.showToast`, 1200ms) then reloads via `window.location.reload()` after 1300ms. This ensures the form's read-only state reflects the new status immediately. Rejection modal dismisses before the toast fires.

### Tests

[tests/Feature/Cases/AdminViewOnlyTest.php](../../tests/Feature/Cases/AdminViewOnlyTest.php) — 10 tests covering:
- `window.__isReadOnly` formula shape and `CASE_ADMIN_MODE` seeding for each status
- All 5 section-save endpoints: 200 + `{ok:true}` on SUBMITTED cases
- Patient identity lock: `first_name`/`last_name`/`dob` unchanged after `savePatient()`

Pending (not yet written):
- Server-side guard test for APPROVED/REJECTED edit attempts (currently no guard exists)
- Audit attribution tests (blocked on audit log landing)

## Other admin-only domains

These have **no doctor counterpart** — admin only:

| Domain | Controller |
|---|---|
| Doctor approval lifecycle | [Admin/DoctorController](../../app/Http/Controllers/Admin/DoctorController.php) (`approve`, `reject`, `suspend`, `reactivate`) |
| Per-doctor practice link approval | [Admin/DoctorPracticeController](../../app/Http/Controllers/Admin/DoctorPracticeController.php) |
| Practice management | [Admin/PracticeController](../../app/Http/Controllers/Admin/PracticeController.php) |
| Master data CRUD | [Admin/CountryController](../../app/Http/Controllers/Admin/CountryController.php), [StateController](../../app/Http/Controllers/Admin/StateController.php), [CityController](../../app/Http/Controllers/Admin/CityController.php), [ZipcodeController](../../app/Http/Controllers/Admin/ZipcodeController.php), [ProductCategoryController](../../app/Http/Controllers/Admin/ProductCategoryController.php), [ProductSubcategoryController](../../app/Http/Controllers/Admin/ProductSubcategoryController.php), [ProductController](../../app/Http/Controllers/Admin/ProductController.php), [ScannerController](../../app/Http/Controllers/Admin/ScannerController.php) |
| Admin notifications + profile | [Admin/NotificationController](../../app/Http/Controllers/Admin/NotificationController.php), [Admin/ProfileController](../../app/Http/Controllers/Admin/ProfileController.php) |
| Lookups (typeahead) | [Admin/Ajax/LookupController](../../app/Http/Controllers/Admin/Ajax/LookupController.php) |

These follow a consistent CRUD + AJAX-drawer pattern — see how
`ProductCategoryController` exposes both resource routes and
`ajax/store`, `ajax/bulk`, `ajax/check-unique`, `ajax/{id}` for inline
edits. Drawer-based CRUD is the team's standard for admin masters.

## When parity *isn't* required

Some doctor routes have no admin equivalent and that's by design:
- `POST /dev/practices/request` — only doctors can request to join
- `POST /dev/cases/create` — admin cannot create cases on behalf of a doctor (no doctor session context)
- `POST /dev/profile/photo` — admins have their own profile photo route

Don't reflexively mirror; mirror what the *case data flow* requires.
