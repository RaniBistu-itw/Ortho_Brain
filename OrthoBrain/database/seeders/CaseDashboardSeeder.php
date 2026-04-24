<?php

namespace Database\Seeders;

use App\Models\CaseModel;
use App\Models\Doctor;
use App\Models\Prescription;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Cases-only seeder. Creates the same spread of cases + prescriptions for
 * every Doctor in the DB so the doctor dashboard has realistic data no matter
 * which doctor you log in as.
 *
 * Doctors are seeded separately (DoctorSeeder + AdditionalDoctorsSeeder).
 * Run this after those.
 *
 * Idempotent: case_code is prefixed with the doctor id, so re-seeding updates
 * rows in place.
 */
class CaseDashboardSeeder extends Seeder
{
    /**
     * Per-doctor case mix. Tuple shape:
     *   [suffix, status, created_days_ago, updated_days_ago, submitted_days_ago|null]
     *
     * Chosen to exercise every dashboard widget:
     *   - all 5 status counts non-zero
     *   - 2 stale DRAFTs (>3 days) to fire the stale-draft alert
     *   - created_at spread across the last 14 days for the pulse sparkline
     *   - REJECTED + APPROVED present for focus items & alerts
     */
    private const CASE_MIX = [
        ['D-001', 'DRAFT',     12, 10, null],
        ['D-002', 'DRAFT',      9,  6, null],
        ['D-003', 'DRAFT',      1,  0, null],

        ['S-001', 'SUBMITTED', 11,  8,  8],
        ['S-002', 'SUBMITTED',  7,  5,  5],
        ['S-003', 'SUBMITTED',  4,  2,  2],
        ['S-004', 'SUBMITTED',  2,  1,  1],

        ['R-001', 'IN_REVIEW', 10,  4,  9],
        ['R-002', 'IN_REVIEW',  6,  3,  5],
        ['R-003', 'IN_REVIEW',  3,  1,  2],

        ['A-001', 'APPROVED',  13,  9, 12],
        ['A-002', 'APPROVED',  10,  7,  9],
        ['A-003', 'APPROVED',   8,  5,  7],
        ['A-004', 'APPROVED',   5,  2,  4],
        ['A-005', 'APPROVED',   2,  0,  1],

        ['X-001', 'REJECTED',   9,  6,  8],
        ['X-002', 'REJECTED',   4,  1,  3],
    ];

    public function run(): void
    {
        $doctors = Doctor::orderBy('id')->get();

        if ($doctors->isEmpty()) {
            $this->command?->warn('CaseDashboardSeeder: no doctors found; run DoctorSeeder / AdditionalDoctorsSeeder first.');
            return;
        }

        $now = Carbon::now();
        $totalCases = 0;

        foreach ($doctors as $doctor) {
            foreach (self::CASE_MIX as [$suffix, $status, $cDays, $uDays, $sDays]) {
                // Prefix keeps case_code unique across doctors and idempotent on rerun.
                $code = sprintf('D%d-%s', $doctor->id, $suffix);

                $createdAt   = $now->copy()->subDays($cDays)->setTime(9, 0);
                $updatedAt   = $now->copy()->subDays($uDays)->setTime(14, 30);
                $submittedAt = $sDays !== null ? $now->copy()->subDays($sDays)->setTime(11, 15) : null;

                $case = CaseModel::withTrashed()->updateOrCreate(
                    ['case_code' => $code],
                    [
                        'doctor_id'    => $doctor->id,
                        'status'       => $status,
                        'submitted_at' => $submittedAt,
                        'created_at'   => $createdAt,
                        'updated_at'   => $updatedAt,
                        'deleted_at'   => null,
                    ]
                );

                Prescription::updateOrCreate(
                    ['case_id' => $case->id],
                    [
                        'arches'                       => 'BOTH',
                        'ipr_enabled'                  => true,
                        'ipr_value'                    => 'DEFER',
                        'attachments_enabled'          => true,
                        'attachments_value'            => 'STEP_1',
                        'elastics_enabled'             => true,
                        'elastics_value'               => 'NO',
                        'extractions_enabled'          => true,
                        'extractions_value'            => 'NO',
                        'tooth_movement_mode'          => 'NONE',
                        'attachment_restrictions_mode' => 'NONE',
                    ]
                );

                $totalCases++;
            }
        }

        $this->command?->info(sprintf(
            'CaseDashboardSeeder: %d cases (+prescriptions) across %d doctors.',
            $totalCases,
            $doctors->count()
        ));
    }
}
