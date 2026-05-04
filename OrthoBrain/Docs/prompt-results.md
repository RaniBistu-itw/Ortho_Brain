# OB Project — Audit Results

## 1. Case Data Persistence Audit (Item 10)

The investigation confirms a "hybrid" persistence state. Some sections are fully integrated with MariaDB, while others exist only in the frontend's temporary storage.

### Persistence Status Matrix

| Section | State Manager | DB Table | API Endpoint | Current Status |
| :--- | :--- | :--- | :--- | :--- |
| **Patient Information** | Alpine.js | `patients` | `POST /dev/cases/{id}/patient` | ✅ **Fully Persistent** |
| **Prescription** | Alpine.js | `prescriptions` | `POST /dev/cases/{id}/prescription` | ✅ **Fully Persistent** |
| **Photographs / X-Rays** | Alpine.js | `case_media` | `POST /dev/cases/{id}/media/upload` | ✅ **Fully Persistent** |
| **Additional Information** | Alpine.js | **NONE** | **MISSING** | ❌ **Local Only** (Saved to `localStorage`) |
| **Impressions** | Alpine.js | **NONE** | **MISSING** | ❌ **Local Only** (Saved to `localStorage`) |
| **Shipping Address** | Alpine.js | **NONE** | **MISSING** | ❌ **Local Only** (Saved to `localStorage`) |

### Root Causes
- **Missing Infrastructure**: No database tables or columns exist for Additional Info, Impressions, or Shipping.
- **Frontend Fallback**: `add-case.js` saves the entire state to `localStorage`. This creates an illusion of persistence that fails across different browsers or on the Admin View Case page.
- **Incomplete Read-Path**: The `CasesController@edit` method does not fetch or serialize the missing sections.

---

## 2. Additional Information Checklist (Item 11)

### Findings
- **Dead Validation Code**: `app/Http/Requests/Cases/AdditionalInformationRequest.php` exists with comprehensive rules, but it is not utilized by any Controller or Route.
- **Mocked Preferences**: The "Doctor's Preferences" (defaults) are preloaded from `resources/js/scripts/cases/mock-preferences.js` instead of the actual `Doctor` model fields (`smile_arc_pref`, `ipr_protocol_pref`, etc.).
- **Unmapped State**: The checklist data in the UI (Diagnosis, History, etc.) has no backend destination.

---

## 3. Recommended Fixes

1. **Database Migration**: Create an `additional_information` table (or similar) to store the case-specific checklist data.
2. **API Integration**: Add the missing methods to `CaseApi.js` and wire them to new Laravel routes.
3. **Preference Preloading**: Refactor the Alpine initialization to use the `caseDoctor` object passed to the view, mapping real database preferences to the form defaults.
4. **Read-Path Serialization**: Update `CasesController` to include the missing sections in the edit-mode prefill.

---

## 4. Shipping Address Data Audit

### Data Availability Matrix

| Source | Exists? | Where stored | Used in registration? | Used in shipping today? |
| :--- | :--- | :--- | :--- | :--- |
| **Doctor Primary Address** | ✅ Yes | `doctors` table (snapshot) | ✅ Yes | ❌ No |
| **Doctor Saved Addresses** | ✅ Yes | `doctor_addresses` table | ❌ No | ❌ No |
| **Practice Addresses** | ✅ Yes | `practices` table | ✅ Yes | ❌ No |
| **Case Shipping Record** | ❌ No | N/A | N/A | ❌ No (localStorage only) |

### Gap Analysis
1. **Persistence Gap**: Shipping data is **localStorage-only** for cases. No database table or API endpoint exists to save case-specific shipping info.
2. **Aggregation Gap**: No single "Address Book" endpoint exists to combine primary, saved, and practice addresses.
3. **Wiring Gap**: The `shipping-address.js` component is hardcoded to `mock-addresses.js`.

### Proposed Work Breakdown
- **Phase 1 (Read Path)**: Aggregation API to fetch all available addresses and wire the frontend dropdown to autofill fields.
- **Phase 2 (Write Path)**: Create `case_shipping_addresses` storage and wire the `saveDraft()` sequence in `add-case.js` to persist selections to the DB.

---

## 6. Form Field UI Consistency Audit

### Per-Item Findings
- **Item 1 (Placeholders)**: Missing on most required text inputs (First Name, Last Name, DOB).
- **Item 3 (Icon Overlap)**: `.voice-input-mic` (right: 0.375rem) overlaps with Bootstrap's `.is-invalid` icon in textareas.
- **Item 4 (Asterisk Gap)**: Space is hardcoded in Blade: `Label <span class="text-danger">*</span>`. Needs removal to become `Label<span...`.
- **Item 5 (Chart ID)**: Input is missing format hint (`PT-1-1-02`).

### Architecture Note
The app uses **duplicated markup** (no shared Blade component for inputs). Fixes for Item 1 and 5 must be applied per-file, while Item 3 and 4 can be addressed via CSS and global find/replace respectively.

### Dark Mode
- **Mic Button**: Background is hardcoded to `#fff`, creating a high-contrast "glare" in dark mode. Requires `.dark-layout` overrides.

### Implementation Strategy
- **CSS**: Fix overlap and dark mode mic colors in `add-case.css`.
- **Blade**: Update `patient-information.blade.php` and others for placeholders and asterisk spacing.


---

## 5. Soft-Delete and Archive Lifecycle Audit

### Current State
- **Soft-Delete Support**: ✅ **High**. Almost all models (`CaseModel`, `Patient`, `Doctor`, `User`, `Practice`) already have `SoftDeletes` traits and `deleted_at` columns.
- **Scheduled Tasks**: ❌ **None**. No tasks are currently defined in `routes/console.php` or `bootstrap/app.php`.

### Critical Finding: Activity Tracking
The current `cases.updated_at` is **insufficient** for tracking inactivity. Child updates (Media, Prescription, Patient) do not currently "touch" the parent `cases` row. A case could be actively edited without the parent timestamp changing.

### Design Recommendation
**Design 3 (Soft-Delete + Retention Window)**:
1. **Touch Logic**: Add `$touches = ['case']` to child models to fix activity tracking.
2. **Soft-Delete**: Cron job soft-deletes drafts after 60 days of inactivity.
3. **Hard-Delete**: Optional hard-purge 60 days after soft-deletion to keep tables light.

### Implementation Prerequisites
- Configure server crontab to run `php artisan schedule:run`.
- Define the cleanup command in `routes/console.php`.

---

## 7. Case List Display Audit (Items 6, 7, 8, 9)

### Findings
- **Patient Name (Items 6 & 7)**: Logic exists in `case-list.blade.php` to show `First Last`, but shows `—` because `patient_id` persistence is missing in the wizard.
- **Practice Name (Item 8)**: A practice switcher already exists in the header, but its visibility is tied to `currentPractice()`. It is missing a fallback for doctors without approved practices.
- **Local Time (Item 9)**: Timestamps are currently server-rendered UTC.

### Recommendation
- **Timezone**: Use **Option A** (UTC storage + JS formatting). Wrap timestamps in a helper that uses `toLocaleString()` to handle user timezones and DST automatically without server-side configuration.
- **Practice Visibility**: Ensure the active practice name is mirrored in the **Sidebar Header** for consistent visibility regardless of the Top Nav state.

---

## 8. PDF Export Audit

### Architecture
- **Library**: DomPDF (`barryvdh/laravel-dompdf`).
- **Template**: `resources/views/content/cases/pdf/case-report.blade.php`.
- **Data Source**: Hybrid (Eloquent for Prescription, `addCaseState` JSON for others).

### Content Gap Analysis

| Expected section | In PDF today? | Notes |
|---|---|---|
| Patient Information | ✅ Yes | Sourced from JSON blob |
| Prescription Details | ✅ Yes | Sourced from Eloquent |
| Additional Info | ✅ Yes | Sourced from JSON blob |
| **Photographs (9 tiles)** | ❌ **No** | **Explicitly deferred** in code |
| **X-Rays (3 tiles)** | ❌ **No** | **Explicitly deferred** in code |
| Shipping Address | ✅ Yes | Sourced from JSON blob |
| Submitter Initials | ✅ Yes | Sourced from JSON blob |

### Critical: Image Rendering
- **Current State**: 0 images embedded.
- **Fix**: Use absolute disk paths (`public_path()`) instead of URLs to bypass DomPDF's remote-loading constraints.

---

## 9. Filtered Dropdown Audit (Items 14 & 15)

### ZIP Code Data (Item 14)
- **Table**: `zipcodes` (608 records) is fully provisioned.
- **Hierarchy**: ✅ `Zipcode` ➔ `City` ➔ `State` ➔ `Country`.
- **Endpoint**: `/dev/zipcodes/search` is functional and returns the full hierarchy for autofill.

### Scanner Data (Item 15)
- **Table**: `scanners` exists but is flat.
- **Gaps**: 
    - ❌ **Doctor Filter**: All scanners are shown globally.
    - ❌ **Hierarchy**: No categories/subcategories exist for grouping.
    - ❌ **UX**: Currently a standard `<select>` without search.

### Proposed Strategy: Shared Component
The app currently has three different ways to handle searchable dropdowns (ZIP, Patient, Select2). 
- **Consolidation**: Create a shared `<x-ob-combobox>` Blade component using Alpine.js.
- **Benefit**: Ensures consistent debounce (250ms), keyboard navigation, and loading states across the Entire platform.

---

## 10. Seeder and Migrator Integrity Audit

### Migration Integrity
- **Result**: ❌ **FAILED** (on SQLite).
- **Critical Bug**: Migration `2026_04_24_100001_add_practice_id_to_cases_table` uses raw MySQL `UPDATE ... JOIN` syntax, which is incompatible with SQLite drivers used for testing.

### Seeder Audit
- **Status**: ✅ **HEALTHY**.
- **List**: 10 seeders correctly sequenced in `DatabaseSeeder.php`.
- **Logic**: `PatientsDemoSeeder` correctly runs before `CaseDashboardSeeder` to maintain relational integrity.

### Doctor Registration Path
- **Users**: ✅ Created with `role=DOCTOR`.
- **Doctors**: ✅ Created with primary address snapshot.
- **Practices**: ✅ Created for "New Practice" registrations.
- **`doctor_addresses`**: ✗ **Skipped by design**. Addresses are snapshotted to the `doctors` table but not inserted into the address book during registration.
- **`doctor_profiles`**: ✗ **N/A**. Data lives directly on the `doctors` table.

### Data Health
- **Orphans**: 0 (No Doctors without Users; no Media without Cases).
- **Counts**: 
    - Users: 14
    - Doctors: 12
    - Cases: 24
    - Patients: 100
    - Media: 129
    - Zipcodes: 608
