> _Last verified: against origin/dev @ `096aea5` on 2026-05-06. If editing, update this stamp._

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
- `POST /dev/cases/{case}/patient` — admin patient changes flow through case edit
- `POST /dev/profile/photo` — admins have their own profile photo route

Don't reflexively mirror; mirror what the *case data flow* requires.
