<?php

namespace App\Http\Controllers;

use App\Models\CaseModel;
use App\Support\ActivePractice;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user   = Auth::user();
        $doctor = $user?->doctor;

        // Scope every query below by $practice when a doctor has an active
        // practice. Falls back to doctor-only scoping when the doctor hasn't
        // selected/been approved for a practice yet (single-practice compat).
        $practice = ActivePractice::get();

        $scoped = fn ($q) => $q
            ->when($doctor,   fn ($q) => $q->where('doctor_id', $doctor->id))
            ->when($practice, fn ($q) => $q->forPractice($practice->id));

        // ─── Case counts by status ───────────────────────────────────────
        $byStatus = CaseModel::query()
            ->tap($scoped)
            ->selectRaw('status, count(*) as n')
            ->groupBy('status')
            ->pluck('n', 'status');

        $staleDraftCount = CaseModel::query()
            ->tap($scoped)
            ->where('status', 'DRAFT')
            ->where('updated_at', '<', now()->subDays(3))
            ->count();

        $stats = [
            'total'     => (int) $byStatus->sum(),
            'draft'     => (int) ($byStatus['DRAFT']     ?? 0),
            'submitted' => (int) ($byStatus['SUBMITTED'] ?? 0),
            'in_review' => (int) ($byStatus['IN_REVIEW'] ?? 0),
            'approved'  => (int) ($byStatus['APPROVED']  ?? 0),
            'rejected'  => (int) ($byStatus['REJECTED']  ?? 0),
        ];

        $activeCount     = $stats['submitted'] + $stats['in_review'] + $stats['approved'];
        $inReviewCount   = $stats['submitted'] + $stats['in_review'];
        $attentionCount  = $stats['rejected'] + $staleDraftCount;

        // ─── Today's Focus (derived actionable items) ────────────────────
        $recentRejected = CaseModel::query()
            ->tap($scoped)
            ->where('status', 'REJECTED')
            ->latest('updated_at')
            ->take(2)
            ->get(['id', 'case_code', 'status', 'updated_at']);

        $recentDrafts = CaseModel::query()
            ->tap($scoped)
            ->where('status', 'DRAFT')
            ->latest('updated_at')
            ->take(2)
            ->get(['id', 'case_code', 'status', 'updated_at']);

        $recentApproved = CaseModel::query()
            ->tap($scoped)
            ->where('status', 'APPROVED')
            ->latest('updated_at')
            ->take(1)
            ->get(['id', 'case_code', 'status', 'updated_at']);

        $focus = collect()
            ->merge($recentRejected->map(fn ($c) => [
                'type'     => 'resubmit',
                'title'    => 'Resubmit required',
                'code'     => $c->case_code ?? ('C-' . str_pad($c->id, 4, '0', STR_PAD_LEFT)),
                'meta'     => 'Reviewer requested changes',
                'when'     => $c->updated_at->diffForHumans(),
                'route'    => route('doctor.cases.edit', $c->id),
                'tone'     => 'danger',
            ]))
            ->merge($recentDrafts->map(fn ($c) => [
                'type'     => 'complete',
                'title'    => 'Finish draft',
                'code'     => $c->case_code ?? ('C-' . str_pad($c->id, 4, '0', STR_PAD_LEFT)),
                'meta'     => 'Unsubmitted · ready to complete',
                'when'     => $c->updated_at->diffForHumans(),
                'route'    => route('doctor.cases.edit', $c->id),
                'tone'     => 'warn',
            ]))
            ->merge($recentApproved->map(fn ($c) => [
                'type'     => 'shipping',
                'title'    => 'Approved — ready',
                'code'     => $c->case_code ?? ('C-' . str_pad($c->id, 4, '0', STR_PAD_LEFT)),
                'meta'     => 'Treatment plan approved',
                'when'     => $c->updated_at->diffForHumans(),
                'route'    => route('doctor.cases.edit', $c->id),
                'tone'     => 'success',
            ]))
            ->take(5)
            ->values();

        // ─── Priority Inbox (alerts) ─────────────────────────────────────
        $alerts = [];
        if ($stats['rejected'] > 0) {
            $alerts[] = [
                'icon'   => 'alert-triangle',
                'tone'   => 'danger',
                'title'  => $stats['rejected'] . ' case' . ($stats['rejected'] > 1 ? 's' : '') . ' rejected',
                'sub'    => 'Reviewer feedback awaits',
                'action' => 'Review',
                'route'  => route('doctor.cases.index') . '?status=REJECTED',
            ];
        }
        if ($staleDraftCount > 0) {
            $alerts[] = [
                'icon'   => 'edit-3',
                'tone'   => 'warn',
                'title'  => $staleDraftCount . ' draft' . ($staleDraftCount > 1 ? 's' : '') . ' stale',
                'sub'    => 'Not updated in 3+ days',
                'action' => 'Resume',
                'route'  => route('doctor.cases.index') . '?status=DRAFT',
            ];
        }
        if ($stats['approved'] > 0) {
            $alerts[] = [
                'icon'   => 'check-circle',
                'tone'   => 'success',
                'title'  => $stats['approved'] . ' approved plan' . ($stats['approved'] > 1 ? 's' : ''),
                'sub'    => 'Ready to begin treatment',
                'action' => 'Open',
                'route'  => route('doctor.cases.index') . '?status=APPROVED',
            ];
        }
        if (empty($alerts)) {
            $alerts[] = [
                'icon'   => 'feather',
                'tone'   => 'neutral',
                'title'  => 'All clear',
                'sub'    => 'No urgent items right now',
                'action' => null,
                'route'  => null,
            ];
        }

        // ─── Treatment Pipeline (latest 5 non-draft cases) ───────────────
        $pipeline = CaseModel::query()
            ->tap($scoped)
            ->whereIn('status', ['SUBMITTED', 'IN_REVIEW', 'APPROVED'])
            ->latest('updated_at')
            ->take(5)
            ->get(['id', 'case_code', 'status', 'submitted_at', 'updated_at'])
            ->map(function ($c) {
                $stageIndex = match ($c->status) {
                    'SUBMITTED' => 1,
                    'IN_REVIEW' => 2,
                    'APPROVED'  => 3,
                    default     => 0,
                };
                return [
                    'id'     => $c->id,
                    'code'   => $c->case_code ?? ('C-' . str_pad($c->id, 4, '0', STR_PAD_LEFT)),
                    'status' => $c->status,
                    'stage'  => $stageIndex,      // 0..4 (Draft, Submitted, In Review, Approved, Shipped)
                    'when'   => $c->updated_at->diffForHumans(),
                    'route'  => route('doctor.cases.edit', $c->id),
                ];
            });

        // ─── Patient Pulse (14-day case activity sparkline) ──────────────
        $start = now()->subDays(13)->startOfDay();
        $raw = CaseModel::query()
            ->tap($scoped)
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as d, count(*) as n')
            ->groupBy('d')
            ->pluck('n', 'd');

        $pulse = [];
        for ($i = 0; $i < 14; $i++) {
            $day = $start->copy()->addDays($i)->toDateString();
            $pulse[] = [
                'date' => $day,
                'n'    => (int) ($raw[$day] ?? 0),
            ];
        }

        return view('dashboard', [
            'doctor'         => $doctor,
            'practice'       => $practice,
            'stats'          => $stats,
            'activeCount'    => $activeCount,
            'inReviewCount'  => $inReviewCount,
            'attentionCount' => $attentionCount,
            'focus'          => $focus,
            'alerts'         => $alerts,
            'pipeline'       => $pipeline,
            'pulse'          => $pulse,
        ]);
    }
}
