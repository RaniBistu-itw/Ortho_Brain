<?php

namespace App\Observers;

use App\Mail\DoctorApprovedMail;
use App\Mail\DoctorRejectedMail;
use App\Models\Doctor;
use Illuminate\Support\Facades\Log;
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

        $mailable = match ($doctor->approval_status) {
            'APPROVED' => new DoctorApprovedMail($doctor),
            'REJECTED' => new DoctorRejectedMail($doctor),
            default    => null,
        };

        if (! $mailable) {
            return;
        }

        dispatch(function () use ($email, $mailable) {
            try {
                Mail::to($email)->send($mailable);
            } catch (\Throwable $e) {
                Log::error('Doctor status mail failed', [
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
            }
        })->afterResponse();
    }
}
