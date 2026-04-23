<?php

namespace App\Notifications;

use App\Models\Practice;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PracticeRequestRejected extends Notification
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
            'title'       => "Rejected at {$this->practice->name}",
            'body'        => $this->reason
                ? "Your request to join {$this->practice->name} was rejected. Reason: {$this->reason}"
                : "Your request to join {$this->practice->name} was rejected.",
            'practice_id' => $this->practice->id,
            'url'         => route('doctor.profile.index') . '?tab=practices',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $msg = (new MailMessage())
            ->subject("Practice request declined: {$this->practice->name}")
            ->greeting('Hello,')
            ->line("Your request to join **{$this->practice->name}** has been declined by the admin.");

        if ($this->reason) {
            $msg->line("**Reason:** {$this->reason}");
        }

        return $msg
            ->action('View All My Practices', route('doctor.profile.index') . '?tab=practices')
            ->line('You can request to join other practices from your profile.')
            ->line('— orthoBrain');
    }
}
