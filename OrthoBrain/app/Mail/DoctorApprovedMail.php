<?php

namespace App\Mail;

use App\Models\Doctor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DoctorApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Doctor $doctor)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your OrthoBrain account is approved',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.doctor-approved',
            with: [
                'firstName' => $this->doctor->first_name,
                'loginUrl'  => url('/login'),
            ],
        );
    }
}
