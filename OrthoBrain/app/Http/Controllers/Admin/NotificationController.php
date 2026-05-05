<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaseModel;
use App\Models\Doctor;
use App\Models\Practice;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

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

        return $doctors->concat($practices)->concat($cases)
            ->sortByDesc('sortAt')
            ->values();
    }
}
