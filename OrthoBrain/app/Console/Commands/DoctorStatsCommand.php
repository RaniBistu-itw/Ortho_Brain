<?php

namespace App\Console\Commands;

use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DoctorStatsCommand extends Command
{
    protected $signature   = 'orthobrain:doctor-stats {doctor? : Doctor ID or partial name — omit to show all doctors}';
    protected $description = 'Print CRM-style case statistics per doctor (total, approval %, rejection %, avg review time, last active).';

    public function handle(): int
    {
        $this->newLine();
        $this->line('  <fg=cyan;options=bold>OrthoBrain — Doctor Stats</>');
        $this->line('  <fg=gray>' . now()->format('Y-m-d H:i:s T') . '</>');
        $this->newLine();

        $input = $this->argument('doctor');

        if ($input === null) {
            return $this->showAll();
        }

        // Numeric → treat as ID, otherwise search by name
        if (ctype_digit((string) $input)) {
            return $this->showById((int) $input);
        }

        return $this->showByName($input);
    }

    // ── Single-doctor view ────────────────────────────────────────────

    private function showById(int $doctorId): int
    {
        $doctor = Doctor::with('user')->find($doctorId);

        if (! $doctor) {
            $this->line("  <fg=red;options=bold>✗</> Doctor #{$doctorId} not found.");
            $this->newLine();
            return self::FAILURE;
        }

        return $this->showDoctor($doctor);
    }

    private function showByName(string $search): int
    {
        $matches = Doctor::with('user')
            ->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name',  'like', "%{$search}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
            })
            ->orderBy('last_name')
            ->get();

        if ($matches->isEmpty()) {
            $this->line("  <fg=red;options=bold>✗</> No doctor found matching <options=bold>\"{$search}\"</>.");
            $this->newLine();
            return self::FAILURE;
        }

        // Exact single match — go straight to detail view
        if ($matches->count() === 1) {
            return $this->showDoctor($matches->first());
        }

        // Multiple matches — show the table for all of them
        $this->line("  <fg=yellow>Found {$matches->count()} doctors matching \"<options=bold>{$search}</>\":</>");
        $this->newLine();

        $rows = $this->computeStats($matches)->map(fn($s) => [
            $s['id'],
            $s['name'],
            $s['total'],
            $s['submitted'],
            $s['approved_pct'] . '%',
            $s['rejected_pct'] . '%',
            $s['avg_review'],
            $s['last_active'],
        ])->toArray();

        $this->table(
            ['ID', 'Doctor', 'Total', 'Submitted', 'Approved %', 'Rejected %', 'Avg Review', 'Last Active'],
            $rows
        );

        $this->newLine();
        $this->line("  <fg=gray>Tip: use the ID for a detailed breakdown — e.g. <options=bold>php artisan orthobrain:doctor-stats 14</></>");
        $this->newLine();

        return self::SUCCESS;
    }

    private function showDoctor(Doctor $doctor): int
    {
        $s = $this->computeStats(collect([$doctor]))->first();

        $this->line("  <options=bold>Doctor:</>  {$s['name']}  <fg=gray>· ID #{$s['id']} · account {$s['account_status']}</>");
        $this->newLine();

        $approvedLine = $s['submitted'] > 0
            ? "{$s['approved']}  <fg=green>({$s['approved_pct']}%)</>"
            : "{$s['approved']}";

        $rejectedLine = $s['submitted'] > 0
            ? "{$s['rejected']}  <fg=red>({$s['rejected_pct']}%)</>"
            : "{$s['rejected']}";

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total cases',     $s['total']],
                ['Submitted',       $s['submitted']],
                ['In Review',       $s['in_review']],
                ['Approved',        strip_tags($approvedLine)],
                ['Rejected',        strip_tags($rejectedLine)],
                ['Avg review time', $s['avg_review']],
                ['Last active',     $s['last_active']],
            ]
        );

        // Colour the approval line below the table where tags render
        $this->line("  Approved: {$approvedLine}   Rejected: {$rejectedLine}");
        $this->newLine();

        return self::SUCCESS;
    }

    // ── All-doctors view ──────────────────────────────────────────────

    private function showAll(): int
    {
        $doctors = Doctor::with('user')->orderBy('id')->get();

        if ($doctors->isEmpty()) {
            $this->line('  <fg=yellow>No doctors found.</>');
            $this->newLine();
            return self::SUCCESS;
        }

        $rows = $this->computeStats($doctors)->map(fn($s) => [
            $s['id'],
            $s['name'],
            $s['total'],
            $s['submitted'],
            $s['approved_pct'] . '%',
            $s['rejected_pct'] . '%',
            $s['avg_review'],
            $s['last_active'],
        ])->toArray();

        $this->table(
            ['ID', 'Doctor', 'Total', 'Submitted', 'Approved %', 'Rejected %', 'Avg Review', 'Last Active'],
            $rows
        );

        $this->newLine();
        $this->line("  <fg=gray>{$doctors->count()} doctor(s) listed.  Run with a doctor ID for a detailed breakdown.</>");
        $this->newLine();

        return self::SUCCESS;
    }

    // ── Stats engine ──────────────────────────────────────────────────

    private function computeStats(Collection $doctors): Collection
    {
        $ids = $doctors->pluck('id')->all();

        // Case counts bucketed by (doctor_id, status)
        $counts = DB::table('cases')
            ->whereIn('doctor_id', $ids)
            ->whereNull('deleted_at')
            ->select('doctor_id', 'status', DB::raw('COUNT(*) as cnt'))
            ->groupBy('doctor_id', 'status')
            ->get()
            ->groupBy('doctor_id');

        // Avg minutes from submitted_at → updated_at for completed cases
        $avgTimes = DB::table('cases')
            ->whereIn('doctor_id', $ids)
            ->whereNull('deleted_at')
            ->whereIn('status', ['APPROVED', 'REJECTED'])
            ->whereNotNull('submitted_at')
            ->select(
                'doctor_id',
                DB::raw('AVG(TIMESTAMPDIFF(MINUTE, submitted_at, updated_at)) as avg_minutes')
            )
            ->groupBy('doctor_id')
            ->get()
            ->keyBy('doctor_id');

        // Most recent case activity per doctor
        $lastActive = DB::table('cases')
            ->whereIn('doctor_id', $ids)
            ->whereNull('deleted_at')
            ->select('doctor_id', DB::raw('MAX(updated_at) as last_at'))
            ->groupBy('doctor_id')
            ->get()
            ->keyBy('doctor_id');

        return $doctors->map(function (Doctor $doctor) use ($counts, $avgTimes, $lastActive) {
            $byStatus = ($counts->get($doctor->id) ?? collect())
                ->pluck('cnt', 'status')
                ->map(fn($v) => (int) $v);

            $total    = $byStatus->sum();
            $draft    = $byStatus->get('DRAFT', 0);
            $approved = $byStatus->get('APPROVED', 0);
            $rejected = $byStatus->get('REJECTED', 0);
            $inReview = $byStatus->get('IN_REVIEW', 0);
            $submitted = $total - $draft;

            $approvedPct = $submitted > 0 ? (int) round($approved / $submitted * 100) : 0;
            $rejectedPct = $submitted > 0 ? (int) round($rejected / $submitted * 100) : 0;

            $avgRow   = $avgTimes->get($doctor->id);
            $avgReview = $avgRow ? $this->formatMinutes((float) $avgRow->avg_minutes) : '—';

            $lastRow       = $lastActive->get($doctor->id);
            $lastActiveStr = $lastRow
                ? Carbon::parse($lastRow->last_at)->diffForHumans()
                : '—';

            $name = trim(($doctor->first_name ?? '') . ' ' . ($doctor->last_name ?? ''));
            if (empty($name)) {
                $name = $doctor->user?->email ?? "Doctor #{$doctor->id}";
            }

            return [
                'id'             => $doctor->id,
                'name'           => $name,
                'account_status' => $doctor->approval_status ?? 'UNKNOWN',
                'total'          => $total,
                'submitted'      => $submitted,
                'approved'       => $approved,
                'rejected'       => $rejected,
                'in_review'      => $inReview,
                'approved_pct'   => $approvedPct,
                'rejected_pct'   => $rejectedPct,
                'avg_review'     => $avgReview,
                'last_active'    => $lastActiveStr,
            ];
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────

    private function formatMinutes(float $minutes): string
    {
        if ($minutes < 1)  return '< 1 min';
        if ($minutes < 60) return (int) round($minutes) . ' min';

        $hours = $minutes / 60;
        if ($hours < 24)   return (int) round($hours) . 'h';

        $days   = (int) floor($hours / 24);
        $remHrs = (int) round($hours - $days * 24);

        return $remHrs > 0 ? "{$days}d {$remHrs}h" : "{$days}d";
    }
}
