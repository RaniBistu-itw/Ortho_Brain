<?php

namespace App\Notifications;

use App\Models\Practice;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PracticeRequestApproved extends Notification
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
            'title'       => "Approved at {$this->practice->name}",
            'body'        => "Your request to join {$this->practice->name} was approved.",
            'practice_id' => $this->practice->id,
            'url'         => route('doctor.profile.index') . '?tab=practices',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject("Practice approved: {$this->practice->name}")
            ->greeting('Good news!')
            ->line("Your request to join **{$this->practice->name}** has been approved.")
            ->line('You can now switch to this practice from your topbar and start working on cases there.')
            ->action('Open My Practices', route('doctor.profile.index') . '?tab=practices')
            ->line('— orthoBrain');
    }
}
