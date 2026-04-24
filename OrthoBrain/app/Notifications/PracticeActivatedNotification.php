<?php

namespace App\Notifications;

use App\Models\Practice;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PracticeActivatedNotification extends Notification
{
    public function __construct(public Practice $practice) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray($notifiable): array
    {
        return [
            'kind'        => 'approved',
            'title'       => "{$this->practice->name} is active again",
            'body'        => "You can now work on cases at {$this->practice->name}.",
            'practice_id' => $this->practice->id,
            'url'         => route('doctor.profile.index') . '?tab=practices',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject("Practice reactivated: {$this->practice->name}")
            ->greeting('Hello,')
            ->line("**{$this->practice->name}** has been reactivated by the admin.")
            ->line('Your access to cases at this practice is restored. Switch to it from the topbar to continue.')
            ->action('Open My Practices', route('doctor.profile.index') . '?tab=practices')
            ->line('— orthoBrain');
    }
}
