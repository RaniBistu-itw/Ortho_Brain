# Notifications — Architecture

Last verified: 2026-05-07 (audited; design locked).

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

All approval/registration related. None case-related as of 2026-05-07.

## Case event notifications (Sprint B-4)

Three new classes to add:

### `CaseApprovedNotification`

- **Trigger:** `Admin/CasesController::updateStatus()` when transitioning IN_REVIEW → APPROVED
- **Recipient:** `case.doctor` (the doctor who owns the case)
- **Body:** "Your case {{ $case->id }} has been approved."
- **Channels:** `via(['database'])`

### `CaseRejectedNotification`

- **Trigger:** `Admin/CasesController::updateStatus()` when transitioning IN_REVIEW → REJECTED
- **Recipient:** `case.doctor`
- **Body (no reason):** "Your case {{ $case->id }} has been unapproved."
- **Body (with reason):** "Your case {{ $case->id }} has been unapproved. Reason: {{ $reason }}."
- **Channels:** `via(['database'])`
- **Note:** depends on Sprint B-3 having shipped the `cases.rejection_reason` column

### `CaseEditedByAdminNotification`

- **Trigger:** Admin section-save endpoints (Sprint B-2). Dispatched once per Save Draft action — single notification listing changed sections, not one per section.
- **Recipient:** `case.doctor`
- **Body:** "Admin edited your case {{ $case->id }}: {{ $sectionList }}." (e.g., "Admin edited your case 42: prescription, photographs.")
- **Channels:** `via(['database'])`
- **Note:** section-level granularity, not field-level. Detail tracking lives in audit log (when shipped), not in the notification body.

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

use Illuminate\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;

class CaseApprovedNotification extends Notification
{
    use Queueable;
    
    public function __construct(public CaseModel $case) {}
    
    public function via($notifiable): array
    {
        return ['database'];
    }
    
    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'case_approved',
            'case_id' => $this->case->id,
            'message' => "Your case {$this->case->id} has been approved.",
        ];
    }
}
```

2. Dispatch from controller:

```php
$case->doctor->notify(new CaseApprovedNotification($case));
```

3. Bell dropdown + modal pick it up automatically via `NotificationController`.

## Audit log relationship

Notifications and audit log are distinct:

- **Notification:** user-facing message, persisted, marked-read by user
- **Audit log:** internal change record, not user-facing, queryable by admin tooling

When the audit log table lands (Sprint B-2 or Phase 2), notifications still fire independently. Don't replace one with the other.
