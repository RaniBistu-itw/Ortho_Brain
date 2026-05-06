> _Last verified: against origin/dev @ `ecbf653` on 2026-05-06. If editing, update this stamp._

# 04 — Add Case Flow

The Add Case wizard is the central feature. Doctors walk through 10
sections to create a case; admins see the same view with extra controls
via `$adminMode`. The flow is Blade + Alpine, not Livewire/SPA.

For the original setup walkthrough, see
[Docs/case-list-add-case-setup.md](../case-list-add-case-setup.md).

## Entry points

- Doctor: `GET /dev/cases/create`, `GET /dev/cases/{case}/edit` →
  [CasesController](../../app/Http/Controllers/CasesController.php) `create()` / `edit()`
- Admin: `GET /admin/cases/{case}/edit` →
  [Admin/CasesController](../../app/Http/Controllers/Admin/CasesController.php) `edit()`

Both render [resources/views/content/cases/add-case.blade.php](../../resources/views/content/cases/add-case.blade.php) — a single template that branches on `$adminMode`.

## The 10 sections

Order is defined inline in [add-case.blade.php](../../resources/views/content/cases/add-case.blade.php) (search `$sections`).
Each has a Blade partial in [sections/](../../resources/views/content/cases/sections/),
a JS module in [resources/js/scripts/cases/sections/](../../resources/js/scripts/cases/sections/),
and (mirrored) [public/js/scripts/cases/sections/](../../public/js/scripts/cases/sections/).

| # | Section | Partial | JS | Persists to |
|---|---|---|---|---|
| 1 | Patient Information | `patient-information.blade.php` | `patient-information.js` | `patients` + `cases.patient_id` |
| 2 | Prescription | `prescription.blade.php` | `prescription.js` | `prescriptions` + `prescription_tooth_restrictions` |
| 3 | Additional Information | `additional-information.blade.php` | `additional-information.js` | `case_additional_info.data` (JSON) |
| 4 | Perfect Smile Plan *(adminOnly)* | `perfect-smile-plan.blade.php` | `perfect-smile-plan.js` | AI narrative output; rendered server-side |
| 5 | Impressions | `impressions.blade.php` | `impressions.js` | `cases.scanner_id`, `cases.impression_method` |
| 6 | Photographs | `photographs.blade.php` | `photographs.js` | `case_media` (section=photograph, 9 tile ids) |
| 7 | X-Rays | `xrays.blade.php` | `xrays.js` | `case_media` (section=xray) |
| 8 | Additional Records *(placeholder)* | `additional-records.blade.php` | — | — |
| 9 | Shipping Address | `shipping-address.blade.php` | `shipping-address.js` | `case_shipping_addresses` |
| 10 | Submit Order | `submit-order.blade.php` | `submit-order.js` | `cases.status` → SUBMITTED, `cases.submitted_at` |

`Perfect Smile Plan` is admin-only — filtered via the `adminOnly` flag
on the section definition. See `add-case.blade.php` `array_filter`.

## Form Requests

Validation is split across two states. Don't assume a `*Request.php`
file under `app/Http/Requests/Cases/` is firing on a live request —
most aren't. Verified against origin/dev `096aea5`:

### In active use (type-hinted into a controller method)

| Request | Wired into |
|---|---|
| [PatientInformationRequest](../../app/Http/Requests/Cases/PatientInformationRequest.php) | [PatientController](../../app/Http/Controllers/PatientController.php) |
| [AdditionalInformationRequest](../../app/Http/Requests/Cases/AdditionalInformationRequest.php) | [CasesController::saveAdditionalInfo](../../app/Http/Controllers/CasesController.php) |

### Defined but not wired

The classes below exist in [app/Http/Requests/Cases/](../../app/Http/Requests/Cases/) and document
intended validation contracts, but are NOT type-hinted into any
controller method on dev as of this verification stamp. Their rules
do NOT fire on the live request flow. They serve as design documents
pending a decision on whether to wire them up at submit-time
validation, OR delete them.

- [PhotographsRequest](../../app/Http/Requests/Cases/PhotographsRequest.php)
- [XRaysRequest](../../app/Http/Requests/Cases/XRaysRequest.php)
- [ImpressionsRequest](../../app/Http/Requests/Cases/ImpressionsRequest.php)
- [PrescriptionRequest](../../app/Http/Requests/Cases/PrescriptionRequest.php)
- [ShippingAddressRequest](../../app/Http/Requests/Cases/ShippingAddressRequest.php)
- [SubmitOrderRequest](../../app/Http/Requests/Cases/SubmitOrderRequest.php)

See validation audit findings #5 (FormRequest wiring strategy
decision) and #1+#2 (silent upload error handling).

## Persistence endpoints

All POST, all under `/dev/cases/{case}/...` (doctor) or
`/admin/cases/{case}/...` (admin). See [routes/web.php](../../routes/web.php) and [Docs/api.md](../api.md) for full contracts.

| Endpoint | Controller method | Section |
|---|---|---|
| `POST /shipping` | `CasesController::saveShipping` | Shipping |
| `POST /impressions` | `CasesController::saveImpressions` | Impressions |
| `POST /additional` | `CasesController::saveAdditionalInfo` | Additional Info |
| `POST /prescription` | [PrescriptionController](../../app/Http/Controllers/PrescriptionController.php)::update | Prescription |
| `POST /patient` | [PatientController](../../app/Http/Controllers/PatientController.php)::upsert | Patient |
| `POST /media/upload` | [CaseMediaController](../../app/Http/Controllers/CaseMediaController.php)::upload | Photos + X-Rays |
| `POST /media/{section}/{tile_id}/destroy` | `CaseMediaController::destroy` | Photos + X-Rays. **POST not DELETE** — PHP 8.3 gotcha |
| `POST /media/reorder` | `CaseMediaController::reorder` | Photos + X-Rays drag-rearrange when tiles are URL-only (no client blob). Throttle 60/1. PR #106. |
| `POST /submit` | `CasesController::submit` | Submit Order |
| `GET\|POST /export.pdf` | [CasePdfController](../../app/Http/Controllers/CasePdfController.php)::export | Final PDF, dompdf-rendered from [pdf/case-report.blade.php](../../resources/views/content/cases/pdf/case-report.blade.php) |

## Components shared across sections

In [content/cases/components/](../../resources/views/content/cases/components/):

- `media-tile.blade.php` — photo/x-ray tile (placeholder + preview img + crop button)
- `crop-modal.blade.php` — Cropper.js modal
- `tooth-picker.blade.php` — tooth selection grid (used in Prescription tooth restrictions)
- `submit-confirm-modal.blade.php` — final-submit confirmation

Helper JS:
- `case-api.js` — fetch wrappers; reads `CASE_API_BASE` set per `$adminMode`
- `case-media-api.js` — media upload/destroy endpoints
- `case-image-store.js` — client-side image state
- `tooth-layout.js` — palmer/universal/FDI numbering layouts
- `voice-input.js` — Web Speech API for additional comments
- `mock-*.js` — placeholder data (addresses, patients, scanners, preferences); should disappear as backend lands

## Identity in CasesController

```php
$doctor = $this->currentDoctor();   // resolves doctors row from Auth::user()
$practiceId = currentPractice()->id; // session-scoped active practice
```

All case queries are scoped:
```php
->where('cases.doctor_id', $doctor->id)
->where('cases.practice_id', $practiceId)
```

**Never** use `Auth::id()` here — see CLAUDE.md Entry 1.

## Alpine wiring conventions

The whole wizard is one Alpine root in `add-case.blade.php`. Each
section is a sub-component. Three patterns to know:

1. **`x-model` + `x-for` options race** — when assigning to a state
   field bound to a `<select>` whose `<option>`s come from
   `<template x-for>`, wrap in `$nextTick`. CLAUDE.md Entry 6 has the
   canonical pattern. Examples: [prescription.js:83](../../resources/js/scripts/cases/sections/prescription.js), [shipping-address.js](../../resources/js/scripts/cases/sections/shipping-address.js).

2. **Hidden file input + synthetic click bubbling** — when calling
   `.click()` on a visually-hidden file input from a parent's
   `@click` handler, add `@click.stop` on the trigger or you'll queue
   a second OS file dialog. See the `feedback_synthetic_click_bubble`
   memory note.

3. **Pre-shell media uploads** — `_persistTile` skips uploads while
   `caseId === 'new'` (no real case ID to attach the row to). After
   [add-case.js](../../resources/js/scripts/cases/add-case.js)
   calls `ensureShellCreated()` and a real case ID is minted, sections
   that hold blobs expose a `flushUnsynced()` hook which iterates
   filled tiles and re-calls `_persistTile()` for each. X-rays
   especially need this — they have no IDB fallback, so without the
   flush a doctor's first save-draft loses every x-ray uploaded
   before the shell existed. Canonical: [photographs.js#_flushUnsynced](../../resources/js/scripts/cases/sections/photographs.js), [xrays.js#_flushUnsynced](../../resources/js/scripts/cases/sections/xrays.js). PR #106.

## Tile drag-reorder semantics

When the doctor drags a photo or x-ray tile onto another, three
states are possible. Picking the wrong persistence path on any of
them is a silent data-loss bug — see CLAUDE.md Entry 11.

| Pre-swap state | Persistence path |
|---|---|
| **Both tiles have client blobs** (uploaded this session) | `destroy` + `upload` on each — blobs re-flow naturally |
| **Both tiles are URL-only** (server prefill, no blob) | `POST /cases/{case}/media/reorder` — server swaps `tile_id` values via a `'__swap__'` placeholder to dodge the `(case_id, section, tile_id)` unique index. Idempotent if source row is missing. |
| **Mixed** (one tile has a blob, one is URL-only) | Fetch the URL-only side into a blob first (`fetch(previewUrl, {credentials: 'same-origin'}).blob()`), re-anchor `previewUrl` to `URL.createObjectURL(blob)`, **then** run `destroy` + `upload`. Without the pre-fetch, the URL-only side gets silently deleted — destroy nukes the row, upload no-ops with no blob to send. |

Why this matters: the old destroy+upload path looked uniform and
worked for the dev-machine case (everything has a blob), but silently
deleted server-only rows when the upload had no blob. The bug only
surfaced when a doctor reopened a case in a fresh browser and
rearranged photos.

Canonical implementations: [photographs.js#_swapOrMoveTiles](../../resources/js/scripts/cases/sections/photographs.js), [xrays.js#_swapOrMoveTiles](../../resources/js/scripts/cases/sections/xrays.js) (PR #106). The xrays
implementation does not yet include the mixed-case URL→blob
pre-fetch — track as a known asymmetry.

## Adding a new section

Use the `add-case-section-scaffold` skill (user-level). It generates:

- Blade partial under `sections/`
- JS module in **both** `resources/js/scripts/cases/sections/` and `public/js/scripts/cases/sections/`
- SCSS under the cases stylesheet
- Form Request stub under `app/Http/Requests/Cases/`
- Section entry in `add-case.blade.php`'s `$sections` array

Then wire the controller method + route by hand (the skill stops short
of touching `routes/web.php` to avoid surprising merges).

## Four-layer audit (CLAUDE.md Entry 4)

When debugging a form-bound field:

1. **Model + migration** — column, type, relationships
2. **Controller serialization** — what the server returns
3. **JS state** — how it's hydrated
4. **Template directives** — `<template x-for>` / `@foreach` rendering

Bugs hide between layers 3 and 4. CLAUDE.md Entry 5 covers field
semantics (e.g. country **code** vs country **name**).

## PDF export

`CasePdfController::export` accepts both GET and POST and renders
[pdf/case-report.blade.php](../../resources/views/content/cases/pdf/case-report.blade.php) via dompdf. Watch for asset URLs — dompdf doesn't fetch over HTTPS reliably; use `public_path()` not `asset()` for embedded images.

## Where things go wrong (cross-references)

| Symptom | Likely cause | Doc |
|---|---|---|
| Endpoint 404s, persistence silent | `Auth::id()` vs `currentDoctor()->id` | CLAUDE.md Entry 1 |
| Admin sees blank section doctor sees populated | Missed admin-side prefill | CLAUDE.md Entry 2, [doc 06](06-admin-parity.md) |
| JS edit not visible after refresh | Forgot to mirror `public/js/` | CLAUDE.md Entry 3, [doc 05](05-frontend-pipeline.md) |
| `<select>` shows blank when data is present | Alpine `x-model` + `x-for` race | CLAUDE.md Entry 6 |
| Behaviour fixed in PR still broken locally | Stale `php artisan serve` | CLAUDE.md Entry 7, `restart-dev-server` skill |
