<?php

namespace App\Notifications;

use App\Models\Practice;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PracticeDeactivatedNotification extends Notification
{
    public function __construct(public Practice $practice) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray($notifiable): array
    {
        return [
            'kind'        => 'practice_inactive',
            'title'       => "{$this->practice->name} is no longer active",
            'body'        => "Access to cases at {$this->practice->name} is paused. Switch to another practice or add a new one to continue.",
            'practice_id' => $this->practice->id,
            'url'         => route('doctor.practices.pending'),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject("Practice paused: {$this->practice->name}")
            ->greeting('Hello,')
            ->line("**{$this->practice->name}** has been set to inactive by the admin.")
            ->line('Your access to cases at this practice is paused until it is reactivated.')
            ->line('You can switch to another practice you have access to, or request to join / add a new one from your profile.')
            ->action('View My Practices', route('doctor.practices.pending'))
            ->line('— orthoBrain');
    }
}
