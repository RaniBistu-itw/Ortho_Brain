<?php

namespace App\Notifications;

use App\Models\CaseModel;
use Illuminate\Notifications\Notification;

class CaseEditedByAdminNotification extends Notification
{
    public function __construct(private CaseModel $case, private string $section) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'kind'  => 'info',
            'title' => 'Case updated by admin',
            'body'  => 'An admin updated the ' . $this->section . ' section of your case #' . $this->case->case_code . '.',
            'url'   => route('doctor.cases.edit', $this->case),
        ];
    }
}
