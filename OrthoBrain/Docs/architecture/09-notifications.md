# Notifications — Architecture

Last verified: 2026-05-11 against origin/dev @ `a0fa974` (B-4a merged — CaseApprovedNotification + CaseRejectedNotification shipped; B-4b open in PR #133).

## Existing infrastructure

Database-backed notifications via Laravel's standard `Illuminate\Notifications` framework.

- Notification classes in `app/Notifications/`
- All currently use `via(['database'])` — no email or push channels wired into the notifications framework
- Surfaced through `NotificationController` (bell dropdown + modal, mark-read, delete)
- Mail infrastructure exists separately in `app/Mail/` (registration, OTP, password reset) but is not wired to the notifications framework

## Existing notification classes

| Class | Domain |
|---|---|
| `DoctorAccountApproved` | Doctor registration |
| `PracticeActivatedNotification` | Practice management |
| `PracticeDeactivatedNotification` | Practice management |
| `PracticeRequestApproved` | Practice membership |
| `PracticeRequestRejected` | Practice membership |
| `PracticeRequestSuspended` | Practice membership |
| `CaseApprovedNotification` | Case lifecycle — **shipped PR #132** |
| `CaseRejectedNotification` | Case lifecycle — **shipped PR #132** |
| `CaseEditedByAdminNotification` | Case lifecycle — **shipped PR #133** |

## Case event notifications (Sprint B-4)

All three classes shipped. `toArray()` shape: `kind`, `title`, `body`, `url`.

### `CaseApprovedNotification` — **shipped PR #132**

- **File:** `app/Notifications/CaseApprovedNotification.php`
- **Trigger:** `Admin/CasesController::updateStatus()` — IN_REVIEW → APPROVED only
- **Recipient:** `case->doctor->user` (eager-loaded via `loadMissing('doctor.user')`)
- **kind:** `'approved'` → renders `bi-check-circle` (green) in bell dropdown
- **Body:** "Your case #{{ $case->case_code }} has been approved."
- **URL:** `route('doctor.cases.edit', $case)`
- **Channels:** `['database']`

### `CaseRejectedNotification` — **shipped PR #132**

- **File:** `app/Notifications/CaseRejectedNotification.php`
- **Trigger:** `Admin/CasesController::updateStatus()` — IN_REVIEW → REJECTED only
- **Recipient:** `case->doctor->user`
- **kind:** `'rejected'` → renders `bi-x-circle` (red) in bell dropdown
- **Body (no reason):** "Your case #{{ $case->case_code }} has been unapproved."
- **Body (with reason):** "Your case #{{ $case->case_code }} has been unapproved. Reason: {{ $reason }}."
- **URL:** `route('doctor.cases.edit', $case)`
- **Channels:** `['database']`
- **Note:** `rejection_reason` is read from `$case->rejection_reason` after `$case->update()` has already persisted it; requires B-3's `cases.rejection_reason` column (shipped).

### `CaseEditedByAdminNotification` — **shipped PR #133**

- **File:** `app/Notifications/CaseEditedByAdminNotification.php`
- **Trigger:** dispatched from every admin section-save endpoint. One notification per save action (not batched per session).
  - `Admin/CasesController`: `saveShipping`, `saveImpressions`, `saveAdditionalInfo`, `savePatient`, `saveSubmitOrder`
  - `Admin/CaseMediaController`: `upload`, `destroy`, `reorder` (overrides that call `parent::` then notify)
  - `PrescriptionController::update()`: admin branch only (`$user->role === 'ADMIN'` guard — doctor saves do not self-notify)
- **Recipient:** `case->doctor->user`
- **kind:** `'info'` → renders `bi-info-circle` (sky blue `#0ea5e9`) in bell dropdown
- **section names:** `'shipping'`, `'impressions'`, `'additional information'`, `'patient'`, `'submit order'`, `'prescription'`, `'media'`
- **Body:** "An admin updated the {{ $section }} section of your case #{{ $case->case_code }}."
- **URL:** `route('doctor.cases.edit', $case)`
- **Channels:** `['database']`

## Out-of-scope events (no notification fires)

- SUBMITTED → IN_REVIEW: admin starting review is not doctor-actionable
- APPROVED → IN_REVIEW: admin reconsidering, not doctor-actionable
- REJECTED → IN_REVIEW: admin reconsidering, not doctor-actionable
- Doctor's own actions on their own cases (no self-notification)

If product requirements change and intermediate transitions need notifications, add new classes following the same pattern. Don't filter at the dispatch site — let the read side filter (mark-read, archive).

## Adding a new notification

1. Create class in `app/Notifications/`:

```php
namespace App\Notifications;

use App\Models\CaseModel;
use Illuminate\Notifications\Notification;

class CaseApprovedNotification extends Notification
{
    public function __construct(private CaseModel $case) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'kind'  => 'approved',
            'title' => 'Case approved',
            'body'  => 'Your case #' . $this->case->case_code . ' has been approved.',
            'url'   => route('doctor.cases.edit', $this->case),
        ];
    }
}
```

2. Dispatch from controller (eager-load to avoid N+1):

```php
$case->loadMissing('doctor.user');
$case->doctor?->user?->notify(new CaseApprovedNotification($case));
```

3. Bell dropdown + modal pick it up automatically via `NotificationController`.

## Audit log relationship

Notifications and audit log are distinct:

- **Notification:** user-facing message, persisted, marked-read by user
- **Audit log:** internal change record, not user-facing, queryable by admin tooling

When the audit log table lands (Sprint B-2 or Phase 2), notifications still fire independently. Don't replace one with the other.
