<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaseModel;
use App\Models\Doctor;
use App\Models\Practice;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Today's-activity feed for the admin bell.
     *
     * Items are derived from real records (no DB-backed notifications table),
     * so read/dismiss state is tracked client-side in localStorage and resets
     * naturally each day.
     */
    public function index(): JsonResponse
    {
        $items = $this->collect();

        return response()->json([
            'items'      => $items,
            'totalToday' => $items->count(),
        ]);
    }

    public function collect()
    {
        $today = Carbon::today();

        $doctors = Doctor::with('user')
            ->whereDate('created_at', $today)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($d) => [
                'key'    => "doctor:{$d->id}",
                'kind'   => 'doctor',
                'title'  => 'New doctor registered',
                'body'   => trim("{$d->first_name} {$d->last_name}") ?: ($d->user?->email ?? 'A new doctor'),
                'time'   => $d->created_at->diffForHumans(),
                'sortAt' => $d->created_at->toIso8601String(),
                'url'    => route('admin.doctors.show', $d->id),
            ]);

        $practices = Practice::whereDate('created_at', $today)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($p) => [
                'key'    => "practice:{$p->id}",
                'kind'   => 'practice',
                'title'  => 'New practice added',
                'body'   => $p->name,
                'time'   => $p->created_at->diffForHumans(),
                'sortAt' => $p->created_at->toIso8601String(),
                'url'    => route('admin.practices.show', $p->id),
            ]);

        $cases = CaseModel::with('doctor')
            ->whereDate('created_at', $today)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($c) {
                $code   = $c->case_code ?: ('Case #' . $c->id);
                $doctor = $c->doctor ? trim("{$c->doctor->first_name} {$c->doctor->last_name}") : '';
                return [
                    'key'    => "case:{$c->id}",
                    'kind'   => 'case',
                    'title'  => 'New case added',
                    'body'   => $doctor ? "{$code} · by {$doctor}" : $code,
                    'time'   => $c->created_at->diffForHumans(),
                    'sortAt' => $c->created_at->toIso8601String(),
                    'url'    => route('admin.cases.edit', $c->id),
                ];
            });

        // Doctor-initiated cancellations of pending practice requests today.
        // CANCELLED is terminal in the pivot's state machine, so updated_at
        // is effectively the cancellation time.
        $cancellations = DB::table('doctor_practice')
            ->join('doctors', 'doctors.id', '=', 'doctor_practice.doctor_id')
            ->join('practices', 'practices.id', '=', 'doctor_practice.practice_id')
            ->where('doctor_practice.approval_status', 'CANCELLED')
            ->whereDate('doctor_practice.updated_at', $today)
            ->orderByDesc('doctor_practice.updated_at')
            ->select(
                'doctor_practice.id as link_id',
                'doctor_practice.practice_id',
                'doctor_practice.updated_at as cancelled_at',
                'doctors.first_name',
                'doctors.last_name',
                'practices.name as practice_name'
            )
            ->get()
            ->map(function ($r) {
                $doctorName  = trim("{$r->first_name} {$r->last_name}") ?: 'A doctor';
                $cancelledAt = Carbon::parse($r->cancelled_at);
                return [
                    'key'    => "practice-cancel:{$r->link_id}",
                    'kind'   => 'practice_cancel',
                    'title'  => 'Doctor cancelled practice request',
                    'body'   => "{$doctorName} withdrew their request to join {$r->practice_name}",
                    'time'   => $cancelledAt->diffForHumans(),
                    'sortAt' => $cancelledAt->toIso8601String(),
                    'url'    => route('admin.practices.show', $r->practice_id),
                ];
            });

        return $doctors->concat($practices)->concat($cases)->concat($cancellations)
            ->sortByDesc('sortAt')
            ->values();
    }
}
