> _Last verified: against origin/dev @ `096aea5` on 2026-05-06. If editing, update this stamp._

# 03 — Database Schema

44 migrations in [database/migrations/](../../database/migrations/),
grouped here by domain. Authoritative source is the migration files +
the matching models in [app/Models/](../../app/Models/).

## Identity & auth

| Table | Migration | Model | Notes |
|---|---|---|---|
| `users` | `0001_01_01_000000_create_users_table` | [User](../../app/Models/User.php) | Single auth table; `role` enum `ADMIN`/`DOCTOR` |
| `users` (additions) | `2026_04_29_100001_add_email_verification_to_users_table` | | OTP fields, `email_verified_at` |
| `users` (additions) | `2026_04_29_100002_add_failed_login_to_users_table` | | `failed_login_attempts`, `locked_until` (per-account login lockout) |
| `admins` | `2026_04_17_073848_create_admins_table` | [Admin](../../app/Models/Admin.php) | `belongsTo(User)` |
| `doctors` | `2026_04_17_073848_create_doctors_table` | [Doctor](../../app/Models/Doctor.php) | `belongsTo(User)`, `practice_id` added later, primary address columns added 2026-04-29 |
| `cache`, `jobs` | `0001_01_01_00000{1,2}_*` | — | Laravel scaffolding |

`users.id` ≠ `doctors.id` ≠ `admins.id` — see [doc 02](02-request-lifecycle.md) and CLAUDE.md Entry 1.

## Geo masters

| Table | Model | Note |
|---|---|---|
| `countries` | [Country](../../app/Models/Country.php) | Holds ISO `country_code` (`US`, `CA`) — frontend binds to code, not name. CLAUDE.md Entry 5. |
| `states` | [State](../../app/Models/State.php) | |
| `cities` | [City](../../app/Models/City.php) | |
| `zipcodes` | [Zipcode](../../app/Models/Zipcode.php) | Required by `PracticesSeeder`; if missing, run `seed-geo` skill. |

Performance indexes added in `2026_04_30_120000_add_performance_indexes_to_masters_tables`.

## Product masters

| Table | Migration | Model |
|---|---|---|
| `products_category` | `2026_04_17_100005_*` | [ProductCategory](../../app/Models/ProductCategory.php) |
| `products_subcategory` | `2026_04_17_100006_*` | [ProductSubcategory](../../app/Models/ProductSubcategory.php) |
| `products` | `2026_04_17_100007_*` | [Product](../../app/Models/Product.php) |
| `product_images` | `2026_04_21_153137_*` | [ProductImage](../../app/Models/ProductImage.php) |
| `scanners` | `2026_04_17_100008_*` | [Scanner](../../app/Models/Scanner.php) |

## Doctor profile + preferences

| Table | Migration | Model |
|---|---|---|
| `doctor_addresses` | `2026_04_20_100001_*` | [DoctorAddress](../../app/Models/DoctorAddress.php) |
| `modalities` | `2026_04_20_120001_*` | [Modality](../../app/Models/Modality.php) |
| `doctor_modalities` | `2026_04_20_120002_*` | (pivot) |
| `buccal_corridor_options` | `2026_04_20_120003_*` | [BuccalCorridorOption](../../app/Models/BuccalCorridorOption.php) |
| `doctor_buccal_corridors` | `2026_04_20_120004_*` | (pivot) |
| `treatment_modalities` | `2026_04_20_120005_*` | [TreatmentModality](../../app/Models/TreatmentModality.php) |
| `doctor_treatment_modalities` | `2026_04_20_120006_*` | (pivot) |
| `specialties` | `2026_04_20_120007_*` | [Specialty](../../app/Models/Specialty.php) |
| `doctor_specialties` | `2026_04_20_120008_*` | (pivot) |
| `doctors` (primary address) | `2026_04_29_120001_add_primary_address_to_doctors_table` | | Doctor's office address (`primary_address_source`, FKs to geo) |

## Practice & multi-practice membership

| Table | Migration | Model |
|---|---|---|
| `practices` | `2026_04_21_100001_*` | [Practice](../../app/Models/Practice.php) |
| `practices` (logo) | `2026_04_21_200001_add_logo_path_to_practices_table` | | |
| `doctors.practice_id` | `2026_04_21_100002_add_practice_id_to_doctors_table` | | Primary practice FK |
| `doctor_practice` | `2026_04_23_100001_create_doctor_practice_table` | (pivot) | Many-to-many membership; status enum |
| `doctor_practice` (suspended) | `2026_04_24_120001_add_suspended_to_doctor_practice_enum` | | |
| `practices` (phone fix) | `2026_04_27_120001_normalise_practice_phone_country_codes` | | Data migration only |

Active practice for a logged-in doctor is resolved by
[ActivePractice helper](../../app/Support/) and exposed via
`currentPractice()` (see [helpers.php](../../app/Support/helpers.php)).

## Cases — the central feature

| Table | Migration | Model |
|---|---|---|
| `cases` | `2026_04_21_060623_create_cases_table` | [CaseModel](../../app/Models/CaseModel.php) | `id`, `doctor_id` FK, `case_code`, `status` enum, `submitted_at`, soft-deletes |
| `cases.practice_id` | `2026_04_24_100001_add_practice_id_to_cases_table` | | Cases scoped per active practice |
| `cases.scanner_id`, `cases.impression_method` | `2026_05_04_133916_create_case_persistence_tables` | | Impressions section persisted on `cases` |
| `cases.submitter_initials` | `2026_05_05_134515_add_submitter_initials_to_cases_table` | | Submitter initials captured at submit time (PR #103) |
| `case_additional_info` | `2026_05_04_133916_*` | [CaseAdditionalInfo](../../app/Models/CaseAdditionalInfo.php) | JSON `data` column for the checklist |
| `case_shipping_addresses` | `2026_05_04_133916_*` | [CaseShippingAddress](../../app/Models/CaseShippingAddress.php) | Practice/doctor names, address, geo FKs |
| `case_media` | `2026_04_27_124920_create_case_media_table` | [CaseMedia](../../app/Models/CaseMedia.php) | Photographs + x-rays. Tile-id keyed. |
| `prescriptions` | `2026_04_21_060625_create_prescriptions_table` | [Prescription](../../app/Models/Prescription.php) | Unique per case. Arches, IPR, attachments, elastics, extractions, modes, comments, future restorative |
| `prescription_tooth_restrictions` | `2026_04_21_060626_*` | [PrescriptionToothRestriction](../../app/Models/PrescriptionToothRestriction.php) | Per-tooth movement/attachment restrictions |
| `patients` | `2026_04_28_051628_create_patients_table` | [Patient](../../app/Models/Patient.php) | Real persistence layer — name, email, phone, chart_id |
| `cases.patient_id` | `2026_04_28_051629_add_patient_id_to_cases` | | FK from cases to patients |

### `cases.status` enum
`DRAFT → SUBMITTED → IN_REVIEW → APPROVED | REJECTED`. Soft-deletes
enabled. See [CasesController.php](../../app/Http/Controllers/CasesController.php) constants `STATUSES` / `ACTIVE_STATUSES`.

## Notifications

| Table | Migration | Note |
|---|---|---|
| `notifications` | `2026_04_23_100002_create_notifications_table` | Laravel-default polymorphic notifications. Custom notifications under [app/Notifications/](../../app/Notifications/) cover doctor approval + practice request lifecycle. |

## Telescope (dev only)

| Table | Migration |
|---|---|
| `telescope_entries`, etc. | `2026_04_29_103952_create_telescope_entries_table` |

## Persistence-debt callouts

These exist in memory because they're known incomplete:

- **Patients table**: live as of 2026-04-28 (`create_patients_table` +
  `add_patient_id_to_cases`). The older `project_patient_persistence_debt`
  memory note ("patient identity = mock-only") is superseded — verify
  before relying on it.
- **Shipping persistence**: `case_shipping_addresses` exists as of
  2026-05-04. The older `project_shipping_persistence_state` memory note
  ("shipping is localStorage-only") is superseded.

When a memory and a migration disagree, the migration wins.

## Adding a new column — round-trip checklist

CLAUDE.md Entry 8 codifies this. Briefly:

1. Migration adds column
2. Model `$fillable` + `$casts` updated
3. Write paths persist it
4. **All** read paths consume it (edit endpoint, list endpoint, admin parity, PDF, exports)
5. Frontend hydration handles null gracefully
6. Smoke test in PR description (Entry 9)
