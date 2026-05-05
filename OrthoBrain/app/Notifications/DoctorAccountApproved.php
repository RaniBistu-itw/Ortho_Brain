<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class DoctorAccountApproved extends Notification
{
    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'kind'  => 'approved',
            'title' => 'Account approved',
            'body'  => 'Welcome — your account has been approved by an admin.',
            'url'   => route('doctor.profile.index') . '?tab=practices',
        ];
    }
}
