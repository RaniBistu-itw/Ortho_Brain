# Case Workflow — Phase 1

Source of truth for the case state machine, role capabilities, and notification expectations. Plain-English, written for the team (product, QA, engineering onboarding) and for Claude during pre-flight.

Last verified: 2026-05-08 against feat/b2-admin-parity-reload @ `266d312` (B-2 implemented — admin section-save routes, widened __isReadOnly formula, page reload after status change).

## Statuses

| Code | UI Label | Meaning |
|---|---|---|
| DRAFT | Draft | Doctor's working draft. Not yet submitted. Invisible to admin. |
| SUBMITTED | Submitted | Doctor has submitted the case for review. Admin sees it now. |
| IN_REVIEW | In Review | Admin is actively reviewing. |
| APPROVED | Approved | Admin has approved the case. Workflow complete (admin can revisit). |
| REJECTED | Unapproved | Admin has determined the case isn't approvable. Terminal-ish. |

The DB column type is MySQL ENUM. Storage values are the codes (uppercase). UI displays the labels.

## State machine

```
[create] ──▶ DRAFT
              │
              │ doctor.submit()
              ▼
          SUBMITTED ──▶ IN_REVIEW ──┬──▶ APPROVED
                            ▲       │      │
                            │       │      └──▶ IN_REVIEW (admin revisits)
                            │       │
                            │       └──▶ REJECTED
                            │             │
                            └─────────────┘ (admin revisits)
```

## Allowed transitions

| From | To | Fired by | Notification |
|---|---|---|---|
| (none) | DRAFT | Doctor (case create) | None |
| DRAFT | SUBMITTED | Doctor (submit action) | None |
| SUBMITTED | IN_REVIEW | Admin | None |
| IN_REVIEW | APPROVED | Admin | Yes — doctor notified |
| IN_REVIEW | REJECTED | Admin | Yes — doctor notified, optional reason in body |
| APPROVED | IN_REVIEW | Admin | None |
| REJECTED | IN_REVIEW | Admin | None |

## Forbidden transitions

These must be enforced; any code path that allows them is a bug:

- DRAFT → anything except SUBMITTED
- SUBMITTED → DRAFT (no withdraw flow)
- Any non-DRAFT → DRAFT (no path back to editable for the doctor)
- REJECTED → APPROVED directly (must go through IN_REVIEW)
- DRAFT → SUBMITTED while case is invalid (existing validation gate)

## Role capabilities

### Doctor

| Action | DRAFT | SUBMITTED | IN_REVIEW | APPROVED | REJECTED |
|---|---|---|---|---|---|
| View case | ✓ | ✓ | ✓ | ✓ | ✓ |
| Edit case | ✓ | ✗ | ✗ | ✗ | ✗ |
| Submit | ✓ → SUBMITTED | no-op | no-op | no-op | no-op |
| Export PDF | ✓ | ✓ | ✓ | ✓ | ✓ |
| View AI smile plan (when shipped) | n/a | ✓ | ✓ | ✓ | ✓ |
| Withdraw / re-edit | ✗ (no path) | | | | |

Once submitted, the doctor cannot edit the case. There is no withdraw button, no re-edit flow, no SUBMITTED → DRAFT path. If a doctor needs corrections, they ask admin (via existing communication channels) or admin will edit on their behalf.

### Admin

| Action | DRAFT | SUBMITTED | IN_REVIEW | APPROVED | REJECTED |
|---|---|---|---|---|---|
| See case in list | ✗ (filtered out) | ✓ | ✓ | ✓ | ✓ |
| View case | ✗ | ✓ | ✓ | ✓ | ✓ |
| Edit case data | ✗ | ✓ | ✓ | ✓ | ✗ |
| Transition status | n/a | →IN_REVIEW | →APPROVED, →REJECTED | →IN_REVIEW | →IN_REVIEW |
| Export PDF | n/a | ✓ | ✓ | ✓ | ✓ |

Admin's role is review-and-decide with corrective edit capability. Admin acts on the doctor's behalf — case ownership stays with the doctor regardless of admin edits.

### Admin locked fields

`patient.first_name`, `patient.last_name`, `patient.dob` are never editable by admin regardless of case status.

### APPROVED case unlock flow

APPROVED cases are view-only for admin. To edit, admin must transition APPROVED → IN_REVIEW via the status dropdown. The page reloads automatically after the transition. The form becomes editable in IN_REVIEW state.

### Status update UX

After any admin status change, the page reloads automatically (toast + `window.location.reload()`). No stale state after transition.

## Admin edit semantics

When admin edits a case (in any status admin can edit — SUBMITTED, IN_REVIEW, APPROVED), the following are preserved:

- `case.doctor_id` (case ownership)
- `case.practice_id` (practice context)
- `case.submitted_at` (original submission timestamp)
- `case.submitter_initials` (original submitter)

Admin's identity is captured via audit context (when audit log lands) but never replaces these fields.

### Patient identity locked fields

When admin edits patient information, three fields are read-only:

- `patient.first_name`
- `patient.last_name`
- `patient.dob`

These three together form the natural key for patient identity. Locking them prevents admin from accidentally re-keying a case onto a different patient identity. If genuinely the wrong patient was registered, the case is rejected and a new case is created.

All other patient fields are editable by admin: email, phone, gender, chart_id, chief complaint.

## Patient identity model

Patients are scoped to **(doctor_id, practice_id)** — each doctor at each practice has their own patient roster.

- Same person seen by Doctor A at Practice X and Doctor B at Practice X = two separate `patients` rows
- Same person seen by Doctor A at Practice X and Doctor A at Practice Y = two separate `patients` rows
- Natural key for matching: `(first_name, last_name, dob)` within `(doctor_id, practice_id)`

The "existing patient found" prompt (in patient-information.js) only fires for matches within the current doctor+practice scope. Cross-doctor lookup is not supported by design.

Rationale: HIPAA-aligned. No automatic patient sharing across providers. Each doctor maintains their own consented patient relationship.

## Notifications

Three case-related database notifications, surfaced via the existing bell + modal infrastructure:

| Class | Trigger | Body |
|---|---|---|
| `CaseApprovedNotification` | IN_REVIEW → APPROVED | "Your case [ID] has been approved." |
| `CaseRejectedNotification` | IN_REVIEW → REJECTED | "Your case [ID] has been unapproved.[Reason: {reason}.]" |
| `CaseEditedByAdminNotification` | Admin saves any case edit | "Admin edited your case [ID]: [section list]." |

Out of scope (no notification fires):

- SUBMITTED → IN_REVIEW (admin starting review is not doctor-actionable)
- APPROVED → IN_REVIEW (admin reconsidering, not doctor-actionable)
- REJECTED → IN_REVIEW (admin reconsidering, not doctor-actionable)

## What's not yet implemented

As of 2026-05-07, the following gaps exist between this design and the current code:

| Rule | Status | Tracking |
|---|---|---|
| Doctor edit only in DRAFT | NOT enforced (CasesController::edit has no status gate) | Sprint B-1 |
| All section-save endpoints status-gated | NOT enforced (likely accept post-submit edits) | Sprint B-1 |
| submit() idempotent on non-DRAFT | NOT enforced (re-stamps submitted_at) | Sprint B-1 |
| Admin write parity (8 routes for media/section saves) | NOT shipped (admin clicks → 404 silently) | Sprint B-2 |
| Patient identity locked fields | NOT enforced (admin path doesn't have edit endpoints to gate) | Sprint B-2 |
| Rejection reason field + modal | NOT shipped (no `cases.rejection_reason` column) | Sprint B-3 |
| Case event notifications | NOT shipped (no notification classes for case events) | Sprint B-4 |
| Removal of dead mock-patients.js | NOT shipped (vestigial) | Hygiene PR |

Sprint sequence is documented in the team's project tracker.
