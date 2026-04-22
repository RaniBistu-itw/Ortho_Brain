<?php

namespace App\Mail;

use App\Models\Doctor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Doctor $doctor)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "We've received your OrthoBrain application",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registration-received',
            with: [
                'firstName' => $this->doctor->first_name,
            ],
        );
    }
}
