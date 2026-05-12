<?php

namespace App\Notifications;

use App\Models\CaseModel;
use Illuminate\Notifications\Notification;

// Notifies the case's doctor when admin edits any section.
// Dispatched from every admin section-save endpoint and
// media upload/destroy/reorder endpoints.
// One notification per save action (not batched per session).
// Channel: database only (no mail in Phase 1).
// See Docs/architecture/09-notifications.md.
class CaseEditedByAdminNotification extends Notification
{
    public function __construct(
        private CaseModel $case,
        private string $section
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'kind'  => 'info',
            'title' => 'Case updated by admin',
            // case_code is nullable on legacy rows — fall back to
            // the numeric ID so the notification body is never blank.
            'body'  => 'An admin updated the ' . $this->section
                       . ' section of your case #'
                       . ($this->case->case_code ?? 'Case #' . $this->case->id) . '.',
            'url'   => route('doctor.cases.edit', $this->case),
        ];
    }
}
