<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Practice;
use App\Notifications\PracticeRequestApproved;
use App\Notifications\PracticeRequestRejected;
use App\Notifications\PracticeRequestSuspended;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DoctorPracticeController extends Controller
{
    public function updatePivotStatus(Request $request, Doctor $doctor, int $link)
    {
        $data = $request->validate([
            'status' => 'required|in:APPROVED,REJECTED,SUSPENDED',
            'reason' => 'nullable|string|max:500',
        ]);

        if (in_array($data['status'], ['REJECTED', 'SUSPENDED'], true) && empty($data['reason'])) {
            return response()->json([
                'ok'      => false,
                'message' => 'A reason is required when rejecting or suspending.',
            ], 422);
        }

        $row = DB::table('doctor_practice')
            ->where('id', $link)
            ->where('doctor_id', $doctor->id)
            ->first();

        if (! $row) {
            return response()->json(['ok' => false, 'message' => 'Link not found.'], 404);
        }

        $allowed = [
            'PENDING'   => ['APPROVED', 'REJECTED'],
            'APPROVED'  => ['SUSPENDED'],
            'REJECTED'  => ['APPROVED'],
            'SUSPENDED' => ['APPROVED'],
        ];

        $current = $row->approval_status;
        if (! isset($allowed[$current]) || ! in_array($data['status'], $allowed[$current], true)) {
            return response()->json([
                'ok'      => false,
                'message' => "Cannot change status from {$current} to {$data['status']}.",
            ], 422);
        }

        $adminId = $this->currentAdminId();
        $updates = ['approval_status' => $data['status'], 'updated_at' => now()];

        if ($data['status'] === 'APPROVED') {
            $updates['approved_at']          = now();
            $updates['approved_by_admin_id'] = $adminId;
            $updates['rejected_at']          = null;
            $updates['rejection_reason']     = null;
        } elseif ($data['status'] === 'REJECTED') {
            $updates['rejected_at']          = now();
            $updates['rejection_reason']     = $data['reason'];
            $updates['approved_at']          = null;
            $updates['approved_by_admin_id'] = $adminId;
        } else { // SUSPENDED
            $updates['rejection_reason']     = $data['reason'];
            $updates['approved_by_admin_id'] = $adminId;
        }

        DB::table('doctor_practice')->where('id', $link)->update($updates);

        $practice = Practice::find($row->practice_id);
        if ($practice && $doctor->user) {
            $notification = match ($data['status']) {
                'APPROVED'  => new PracticeRequestApproved($practice),
                'REJECTED'  => new PracticeRequestRejected($practice, $data['reason']),
                'SUSPENDED' => new PracticeRequestSuspended($practice, $data['reason']),
            };
            $this->sendNotificationAfterResponse($doctor->user, $notification);
        }

        return response()->json([
            'ok'     => true,
            'status' => $data['status'],
        ]);
    }

    public function approve(Doctor $doctor, int $link)
    {
        $row = DB::table('doctor_practice')
            ->where('id', $link)
            ->where('doctor_id', $doctor->id)
            ->first();

        if (! $row) {
            return back()->with('error', 'Practice request not found for this doctor.');
        }
        if ($row->approval_status !== 'PENDING') {
            return back()->with('error', 'Only pending requests can be approved.');
        }

        DB::table('doctor_practice')
            ->where('id', $link)
            ->update([
                'approval_status'      => 'APPROVED',
                'approved_at'          => now(),
                'approved_by_admin_id' => $this->currentAdminId(),
                'rejected_at'          => null,
                'rejection_reason'     => null,
                'updated_at'           => now(),
            ]);

        $practice = Practice::find($row->practice_id);
        if ($practice && $doctor->user) {
            $this->sendNotificationAfterResponse($doctor->user, new PracticeRequestApproved($practice));
        }

        return back()->with('success', "Approved {$doctor->first_name} {$doctor->last_name} → {$practice?->name}.");
    }

    public function reject(Request $request, Doctor $doctor, int $link)
    {
        $data = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ], [
            'rejection_reason.required' => 'Please give a reason so the doctor knows why.',
        ]);

        $row = DB::table('doctor_practice')
            ->where('id', $link)
            ->where('doctor_id', $doctor->id)
            ->first();

        if (! $row) {
            return back()->with('error', 'Practice request not found for this doctor.');
        }
        if ($row->approval_status !== 'PENDING') {
            return back()->with('error', 'Only pending requests can be rejected.');
        }

        DB::table('doctor_practice')
            ->where('id', $link)
            ->update([
                'approval_status'      => 'REJECTED',
                'rejected_at'          => now(),
                'rejection_reason'     => $data['rejection_reason'],
                'approved_by_admin_id' => $this->currentAdminId(),
                'approved_at'          => null,
                'updated_at'           => now(),
            ]);

        $practice = Practice::find($row->practice_id);
        if ($practice && $doctor->user) {
            $this->sendNotificationAfterResponse($doctor->user, new PracticeRequestRejected($practice, $data['rejection_reason']));
        }

        return back()->with('success', 'Practice request rejected.');
    }

    private function currentAdminId(): ?int
    {
        return Auth::user()?->admin?->id;
    }

    private function sendNotificationAfterResponse($notifiable, $notification): void
    {
        dispatch(function () use ($notifiable, $notification) {
            try {
                $notifiable->notify($notification);
            } catch (\Throwable $e) {
                Log::error('Practice status notification failed', [
                    'notifiable_id' => $notifiable->getKey(),
                    'notification'  => get_class($notification),
                    'error'         => $e->getMessage(),
                ]);
            }
        })->afterResponse();
    }
}
