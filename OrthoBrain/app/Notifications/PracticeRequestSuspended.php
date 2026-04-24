<?php

namespace App\Notifications;

use App\Models\Practice;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PracticeRequestSuspended extends Notification
{
    public function __construct(public Practice $practice, public ?string $reason = null) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray($notifiable): array
    {
        return [
            'kind'        => 'rejected',
            'title'       => "Suspended at {$this->practice->name}",
            'body'        => $this->reason
                ? "Your access to {$this->practice->name} has been suspended. Reason: {$this->reason}"
                : "Your access to {$this->practice->name} has been suspended.",
            'practice_id' => $this->practice->id,
            'url'         => route('doctor.profile.index') . '?tab=practices',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $msg = (new MailMessage())
            ->subject("Practice access suspended: {$this->practice->name}")
            ->greeting('Hello,')
            ->line("Your access to **{$this->practice->name}** has been suspended by the admin.");

        if ($this->reason) {
            $msg->line("**Reason:** {$this->reason}");
        }

        return $msg
            ->action('View My Practices', route('doctor.profile.index') . '?tab=practices')
            ->line('If you believe this was in error, contact the admin.')
            ->line('— orthoBrain');
    }
}
