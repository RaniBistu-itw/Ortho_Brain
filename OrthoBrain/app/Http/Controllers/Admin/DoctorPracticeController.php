<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Practice;
use App\Notifications\PracticeRequestApproved;
use App\Notifications\PracticeRequestRejected;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Per-doctor-practice-link approval actions, embedded inside the existing
 * admin doctor show page. No standalone index — admin reaches these by
 * clicking into a doctor first. Kept deliberately small to minimise merge
 * conflicts with the teammate's /admin/practices/* work.
 */
class DoctorPracticeController extends Controller
{
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
            $doctor->user->notify(new PracticeRequestApproved($practice));
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
            $doctor->user->notify(new PracticeRequestRejected($practice, $data['rejection_reason']));
        }

        return back()->with('success', 'Practice request rejected.');
    }

    private function currentAdminId(): ?int
    {
        return Auth::user()?->admin?->id;
    }
}
