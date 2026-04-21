# Doctor Management — Admin Panel Design

**Date:** 2026-04-21
**Owner:** OrthoBrain admin panel
**Status:** Approved for implementation

## Goal

Give admins a production-grade surface to review, search, approve, reject, and
edit doctor records in the OrthoBrain admin panel, matching the existing Vuexy
theme conventions already in use across `admin.product-subcategories`,
`admin.products`, `admin.scanners`, etc.

## Scope

- New sidebar section `Users` with a `Doctors` nav item.
- Listing page at `/admin/doctors` with status tabs (Pending / Approved /
  Rejected / Suspended / All), filters, search, pagination.
- Detail page at `/admin/doctors/{doctor}` with per-card read-and-edit.
- Approval workflow (approve, reject with required reason, suspend/reactivate).
- Soft delete from the detail page.
- No admin-side doctor creation (doctors self-register).

## Non-goals

- New CSS design tokens or libraries.
- Editing doctor addresses inline (addresses shown read-only; owned by the
  doctor profile flow).
- Editing `Practice` records from this surface.

## Data model context

`App\Models\Doctor` has 4 approval states (`PENDING`, `APPROVED`, `REJECTED`,
`SUSPENDED`), soft deletes, a `user` / `practice` belongsTo, 4 many-to-many
relationships (`modalities`, `specialties`, `treatmentModalities`,
`buccalCorridorOptions`), and `hasMany` addresses split by `shipping` /
`billing` type. See `database/migrations/2026_04_17_073848_create_doctors_table.php`.

## Sidebar

Insert a new collapsible section in `resources/views/partials/sidebar.blade.php`
between `Manage Types` and `Locations`:

```
Users  (ob-section-head)
  Doctors   ← Feather icon: user-check
```

Uses the existing `ob-section-head` / `ob-chevron` / `obSidebarToggle()`
mechanism — no CSS additions.

## Listing page — `/admin/doctors`

### Layout

Extends `layouts.admin`. Single `card` following the subcategories pattern:

- `card-header`: title "Doctors" + right-side pill
  `{{ $pendingCount }} pending review` (links to `?status=PENDING`), only
  rendered when `$pendingCount > 0`.
- Below header: status tabs using existing `nav nav-tabs` — Pending /
  Approved / Rejected / Suspended / All. Current tab is driven by `?status=`.
- Filter bar (`card-body py-1`): practice select (`js-searchable`), text search,
  Clear link. Auto-submits via `obAutoFilter`.
- Table: `table table-hover`, 6 columns (Doctor, Practice, Contact, Registered,
  Status, Actions).
- Pagination via `$doctors->links()`.

### Columns

| # | Header | Source |
|---|---|---|
| 1 | Doctor | avatar (photo or initials) + `Dr. {first_name} {last_name}` bold + `other_email` muted |
| 2 | Practice | `$doctor->practice?->name ?? '—'` |
| 3 | Contact | `doctor_contact_email` + `doctor_cell_phone` muted |
| 4 | Registered | `$doctor->created_at->diffForHumans()` with tooltip on exact date |
| 5 | Status | `badge rounded-pill badge-light-*` keyed by `approval_status` |
| 6 | Actions | View icon → detail page |

Status → badge color map:
- `PENDING`  → `badge-light-warning`
- `APPROVED` → `badge-light-success`
- `REJECTED` → `badge-light-danger`
- `SUSPENDED` → `badge-light-secondary`

### Controller behavior

`DoctorController@index`:
- Eager loads `practice`.
- Scopes: `where('approval_status', …)` when `status` in the allowed set;
  `when('practice_id')` filter; `search` spans `first_name`, `last_name`,
  `doctor_contact_email`, `other_email`, `doctor_cell_phone`.
- Orders by `created_at desc`.
- Paginates 15, `->withQueryString()`.
- Passes `$pendingCount = Doctor::where('approval_status','PENDING')->count()`.
- Passes `$practices = Practice::orderBy('name')->get(['id','name'])`.
- Passes `$currentStatus` for tab highlighting.

## Detail page — `/admin/doctors/{doctor}`

### Layout

Full-width identity header card, then two-column grid at `≥lg`:

```
┌ Identity Header ─────────────────────────────────────────┐
│ [Avatar] Name + status badge    [Approve] [Reject] [⋯]   │
│          email · phone · practice                        │
└──────────────────────────────────────────────────────────┘
┌ col-lg-8 ───────────────────────┐  ┌ col-lg-4 ───────────┐
│ Card: Contact & Identity        │  │ Card: Approval      │
│ Card: Treatment Preferences     │  │ Card: Practice      │
│ Card: Clinical Preferences      │  │ Card: Addresses     │
│ Card: Ortho Services            │  │                     │
└─────────────────────────────────┘  └─────────────────────┘
```

### Sections (cards)

1. **Identity header** — avatar (80px), name, status badge, approve/reject
   buttons (shown only when `PENDING`), suspend/reactivate in a menu.
2. **Contact & Identity** — first_name, last_name, preferred_language,
   doctor_contact_email, doctor_cell_phone, other_email, preferred_contact_mode,
   currently_providing_ortho_services (toggle).
3. **Treatment Preferences** — preferred_tooth_numbering_system, smile_arc_pref,
   small_lateral_incisors_pref, mixed_dentition_pref,
   orthodontic_extractions_pref, ipr_protocol_pref + ipr_protocol_other_note.
4. **Clinical Preferences** — elastics_bonded_buttons_pref,
   extractions_if_suggested_pref, attachment_stage_pref.
5. **Ortho Services** — specialties, modalities, treatment_modalities,
   buccal_corridor_options (pill checkbox groups in edit mode).
6. **Approval** (right rail, status-accented border) — approval_status,
   approved_at, approved_by_admin_id → name, rejection_reason. Read-only here;
   changes flow through the approve/reject/suspend modals.
7. **Practice** (right rail) — name, website, phone. Read-only (editing
   practices is out of scope for this feature).
8. **Addresses** (right rail) — shipping and billing, read-only.

### Editing UX

Per-card edit mode. Each editable card's header has a pencil-icon button
(`btn btn-icon btn-sm btn-outline-primary`). Clicking it:

- Swaps the card body to a form (POST to `admin.doctors.update` with a
  `section=` key so the controller only validates that slice).
- Other cards remain read-only.
- Card footer shows `[Cancel]` + `[Save changes]`.
- Success → redirect back to show page with flash via `partials.flash`.

Rationale: 35+ fields on one form is unusable; per-field inline edit is noisy
for enum-heavy data. Per-card edit maps to the user's mental grouping.

### Approve / Reject / Suspend

- **Approve**: `POST admin.doctors.approve`. SweetAlert confirm. Sets status,
  `approved_at`, `approved_by_admin_id`. Clears `rejection_reason`.
- **Reject**: Bootstrap modal with required `rejection_reason` textarea. `POST
  admin.doctors.reject`.
- **Suspend / Reactivate**: confirm modal. Toggles between APPROVED and
  SUSPENDED. Requires previously approved.

## Routes

```php
Route::resource('doctors', DoctorController::class)
    ->only(['index','show','update','destroy'])
    ->parameter('doctors','doctor');
Route::post('doctors/{doctor}/approve',   [DoctorController::class,'approve'])->name('doctors.approve');
Route::post('doctors/{doctor}/reject',    [DoctorController::class,'reject'])->name('doctors.reject');
Route::post('doctors/{doctor}/suspend',   [DoctorController::class,'suspend'])->name('doctors.suspend');
Route::post('doctors/{doctor}/reactivate',[DoctorController::class,'reactivate'])->name('doctors.reactivate');
```

## Form request

`App\Http\Requests\Admin\UpdateDoctorSectionRequest` accepts a `section` field
(`contact | preferences | clinical | ortho`) and validates only the fields
that belong to that section, using `Rule::in(...)` against the enum values in
the migration. Keeps controller lean and validation colocated.

## File layout

```
app/Http/Controllers/Admin/DoctorController.php
app/Http/Requests/Admin/UpdateDoctorSectionRequest.php
app/Http/Requests/Admin/RejectDoctorRequest.php

resources/views/admin/doctors/
  index.blade.php
  show.blade.php
  _partials/
    row.blade.php                 ← one table row (keeps index tidy)
    header.blade.php              ← identity header + action buttons
    card-contact.blade.php
    card-preferences.blade.php
    card-clinical.blade.php
    card-ortho.blade.php
    card-practice.blade.php
    card-addresses.blade.php
    card-approval.blade.php
    reject-modal.blade.php
    suspend-modal.blade.php
    field.blade.php               ← {label,value,edit input} reused per section

resources/views/partials/sidebar.blade.php   ← add Users/Doctors entry
routes/web.php                              ← add doctor routes
```

## Success criteria

- List page renders with filters + pagination, matching existing table visuals.
- Status tabs switch correctly via querystring.
- Detail page renders all 8 cards with real data, no layout breakage at mobile.
- Approve / reject / suspend flows persist correctly and show flash feedback.
- Per-card edit saves and re-displays without losing context.
- Pending count pill updates after an approval.
- No new CSS tokens introduced beyond a tiny avatar-initials utility.
