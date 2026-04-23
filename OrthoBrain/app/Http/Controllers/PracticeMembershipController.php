<?php

namespace App\Http\Controllers;

use App\Models\Practice;
use App\Support\ActivePractice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PracticeMembershipController extends Controller
{
    /**
     * Doctor switches the active practice for this session.
     * Only practices they have an APPROVED link to are valid targets.
     */
    public function switch(Request $request)
    {
        $data = $request->validate([
            'practice_id' => 'required|integer|exists:practices,id',
        ]);

        if (! ActivePractice::set((int) $data['practice_id'])) {
            return back()->with('error', 'You do not have access to that practice.');
        }

        return back()->with('success', 'Switched practice.');
    }

    /**
     * Landing page when the doctor is approved but has no active practice links yet.
     */
    public function pending()
    {
        $doctor = Auth::user()?->doctor;
        abort_unless($doctor, 403);

        $pending  = $doctor->pendingPractices()->get();
        $rejected = $doctor->rejectedPractices()->get();

        return view('doctor.practices.pending', [
            'pending'  => $pending,
            'rejected' => $rejected,
        ]);
    }

    /**
     * Doctor requests to join an existing practice (post-registration path).
     * New pivot row, status PENDING.
     */
    public function request(Request $request)
    {
        $data = $request->validate([
            'practice_id' => 'required|integer|exists:practices,id',
        ]);

        $doctor = Auth::user()?->doctor;
        abort_unless($doctor, 403);

        $exists = DB::table('doctor_practice')
            ->where('doctor_id', $doctor->id)
            ->where('practice_id', $data['practice_id'])
            ->whereIn('approval_status', ['PENDING', 'APPROVED'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'You already have a pending or active link to this practice.');
        }

        $doctor->practices()->attach($data['practice_id'], [
            'approval_status' => 'PENDING',
            'is_primary'      => false,
            'requested_at'    => now(),
        ]);

        return back()->with('success', 'Practice request submitted for admin review.');
    }

    /**
     * Doctor cancels a pending request of their own.
     */
    public function cancel(int $link)
    {
        $doctor = Auth::user()?->doctor;
        abort_unless($doctor, 403);

        $row = DB::table('doctor_practice')->where('id', $link)->first();
        abort_unless($row && (int) $row->doctor_id === $doctor->id, 404);

        if ($row->approval_status !== 'PENDING') {
            return back()->with('error', 'Only pending requests can be cancelled.');
        }

        DB::table('doctor_practice')->where('id', $link)->update([
            'approval_status' => 'CANCELLED',
            'updated_at'      => now(),
        ]);

        return back()->with('success', 'Request cancelled.');
    }

    /**
     * Doctor leaves a practice they were active at.
     * Cannot leave the only active practice (would orphan their session).
     */
    public function leave(int $link)
    {
        $doctor = Auth::user()?->doctor;
        abort_unless($doctor, 403);

        $row = DB::table('doctor_practice')->where('id', $link)->first();
        abort_unless($row && (int) $row->doctor_id === $doctor->id, 404);

        if ($row->approval_status !== 'APPROVED') {
            return back()->with('error', 'Only active links can be left.');
        }

        $activeCount = $doctor->activePractices()->count();
        if ($activeCount <= 1) {
            return back()->with('error', 'You cannot leave your only active practice.');
        }

        DB::table('doctor_practice')->where('id', $link)->update([
            'approval_status' => 'LEFT',
            'left_at'         => now(),
            'is_primary'      => false,
            'updated_at'      => now(),
        ]);

        // If they left their currently-active session practice, drop the session pointer.
        if (session(ActivePractice::SESSION_KEY) == $row->practice_id) {
            ActivePractice::clear();
        }

        return back()->with('success', 'You have left this practice.');
    }

    /**
     * Doctor marks a different active link as their primary.
     */
    public function makePrimary(int $link)
    {
        $doctor = Auth::user()?->doctor;
        abort_unless($doctor, 403);

        $row = DB::table('doctor_practice')->where('id', $link)->first();
        abort_unless($row && (int) $row->doctor_id === $doctor->id, 404);

        if ($row->approval_status !== 'APPROVED') {
            return back()->with('error', 'Only active practice links can be set as primary.');
        }

        DB::transaction(function () use ($doctor, $link) {
            DB::table('doctor_practice')
                ->where('doctor_id', $doctor->id)
                ->update(['is_primary' => false, 'updated_at' => now()]);

            DB::table('doctor_practice')
                ->where('id', $link)
                ->update(['is_primary' => true, 'updated_at' => now()]);
        });

        return back()->with('success', 'Primary practice updated.');
    }
}
