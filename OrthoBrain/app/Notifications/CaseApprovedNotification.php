<?php

namespace App\Notifications;

use App\Models\CaseModel;
use Illuminate\Notifications\Notification;

// Notifies the case's doctor when admin approves their case.
// Dispatched from Admin/CasesController::updateStatus()
// on IN_REVIEW → APPROVED transition only.
// Channel: database only. See Docs/architecture/09-notifications.md.
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
            // case_code is nullable on legacy rows — fall back to
            // the numeric ID so the notification body is never blank.
            'body'  => 'Your case #' . ($this->case->case_code ?? 'Case #' . $this->case->id) . ' has been approved.',
            'url'   => route('doctor.cases.edit', $this->case),
        ];
    }
}
