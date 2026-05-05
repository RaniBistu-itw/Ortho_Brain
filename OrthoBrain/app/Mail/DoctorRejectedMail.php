<?php

namespace App\Mail;

use App\Models\Doctor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DoctorRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Doctor $doctor)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Update on your OrthoBrain application',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.doctor-rejected',
            with: [
                'firstName'  => $this->doctor->first_name,
                'reason'     => $this->doctor->rejection_reason ?? 'No reason provided.',
                'reapplyUrl' => url('/register'),
            ],
        );
    }
}
