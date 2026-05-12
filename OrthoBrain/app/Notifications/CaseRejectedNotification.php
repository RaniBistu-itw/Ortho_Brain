<?php

namespace App\Notifications;

use App\Models\CaseModel;
use Illuminate\Notifications\Notification;

// Notifies the case's doctor when admin rejects their case.
// Dispatched from Admin/CasesController::updateStatus()
// on IN_REVIEW → REJECTED transition only.
// Carries rejection_reason (nullable) from cases table.
// Channel: database only. See Docs/architecture/09-notifications.md.
class CaseRejectedNotification extends Notification
{
    public function __construct(
        private CaseModel $case,
        private ?string $reason = null
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        // case_code is nullable on legacy rows — fall back to
        // the numeric ID so the notification body is never blank.
        $body = 'Your case #' . ($this->case->case_code ?? 'Case #' . $this->case->id) . ' has been unapproved.';
        if ($this->reason) {
            $body .= ' Reason: ' . $this->reason;
        }
        return [
            'kind'  => 'rejected',
            'title' => 'Case unapproved',
            'body'  => $body,
            'url'   => route('doctor.cases.edit', $this->case),
        ];
    }
}
