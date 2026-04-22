<?php

namespace App\Observers;

use App\Mail\DoctorApprovedMail;
use App\Mail\DoctorRejectedMail;
use App\Models\Doctor;
use Illuminate\Support\Facades\Mail;

class DoctorObserver
{
    public function updated(Doctor $doctor): void
    {
        if (! $doctor->wasChanged('approval_status')) {
            return;
        }

        $email = $doctor->user?->email;
        if (! $email) {
            return;
        }

        match ($doctor->approval_status) {
            'APPROVED' => Mail::to($email)->send(new DoctorApprovedMail($doctor)),
            'REJECTED' => Mail::to($email)->send(new DoctorRejectedMail($doctor)),
            default    => null,
        };
    }
}
