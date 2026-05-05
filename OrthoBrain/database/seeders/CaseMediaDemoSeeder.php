<?php

namespace Database\Seeders;

use App\Models\CaseMedia;
use App\Models\CaseModel;
use App\Models\Patient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Seeds case_media (server-side photographs + x-rays) and backfills the
 * cases.patient_id FK so existing demo cases hydrate with real data on
 * the wizard. Uses the bundled tile placeholders from
 * public/images/case-placeholders/ as the source images so we don't need
 * to ship binary fixtures.
 *
 * Idempotent:
 *   - cases.patient_id is set only when null (won't overwrite real links)
 *   - case_media is upserted by (case_id, section, tile_id)
 *   - files are copied with a deterministic destination filename so
 *     reseeding doesn't blow up storage
 *
 * Skips media seeding for cases that already have media (e.g. cases the
 * user uploaded photos to via the actual wizard flow).
 *
 * Run order: AFTER PatientsDemoSeeder + CaseDashboardSeeder.
 */
class CaseMediaDemoSeeder extends Seeder
{
    private const PHOTO_TILES = [
        'profile'           => 'placeholder-1.jpg',
        'frontal-rest'      => 'placeholder-2.jpg',
        'frontal-smile'     => 'placeholder-3.jpg',
        'upper-occlusal'    => 'placeholder-4.jpg',
        'frontal-bite'      => 'placeholder-5.jpg',
        'lower-occlusal'    => 'placeholder-6.jpg',
        'right-buccal'      => 'placeholder-7.jpg',
        'frontal-retracted' => 'placeholder-8.jpg',
        'left-buccal'       => 'placeholder-9.jpg',
    ];

    private const XRAY_TILES = [
        'lateral-ceph'      => 'xray-photo-01.jpg',
        'panoramic'         => 'xray-photo-02.jpg',
        'full-mouth-series' => 'xray-photo-03.jpg',
    ];

    /**
     * Seed media for the first N cases per doctor — enough to make the
     * wizard look populated without bloating storage with a media row
     * for every case in the dashboard seed (26 × 12 doctors = 312).
     */
    private const MAX_MEDIA_CASES_PER_DOCTOR = 5;

    public function run(): void
    {
        $patientCount = Patient::count();
        if ($patientCount === 0) {
            $this->command?->warn('CaseMediaDemoSeeder: no patients; run PatientsDemoSeeder first.');
            return;
        }

        $linked      = $this->backfillPatientIds();
        [$mediaRows, $bytesCopied, $casesTouched] = $this->seedMediaForCases();

        $this->command?->info(sprintf(
            'CaseMediaDemoSeeder: %d cases linked to patients, %d media rows for %d cases (%s of placeholders copied).',
            $linked,
            $mediaRows,
            $casesTouched,
            $this->formatBytes($bytesCopied)
        ));
    }

    private function backfillPatientIds(): int
    {
        $linked = 0;
        $cases = CaseModel::whereNull('patient_id')->orderBy('id')->get();

        foreach ($cases as $case) {
            // Pick a deterministic patient from THIS doctor's roster within
            // the case's practice. Mod by id keeps the same case → same
            // patient across reseeds (idempotent).
            $patientIds = Patient::where('doctor_id', $case->doctor_id)
                ->where('practice_id', $case->practice_id)
                ->orderBy('id')
                ->pluck('id')
                ->all();

            if (empty($patientIds)) {
                continue;
            }
            $picked = $patientIds[$case->id % count($patientIds)];
            $case->patient_id = $picked;
            $case->saveQuietly(); // don't bump updated_at; preserves dashboard-seeder's deliberate timestamps
            $linked++;
        }

        return $linked;
    }

    /**
     * @return array{0: int, 1: int, 2: int} — [media rows, bytes copied, cases touched]
     */
    private function seedMediaForCases(): array
    {
        $disk = Storage::disk('public');
        $photoDir = public_path('images/case-placeholders/photographs');
        $xrayDir  = public_path('images/case-placeholders/xrays');

        $mediaRows    = 0;
        $bytesCopied  = 0;
        $casesTouched = 0;

        $cases = CaseModel::with('media')
            ->orderBy('doctor_id')
            ->orderBy('id')
            ->get();

        // Group cases by doctor so we can apply MAX_MEDIA_CASES_PER_DOCTOR.
        $byDoctor = $cases->groupBy('doctor_id');

        foreach ($byDoctor as $doctorId => $doctorCases) {
            $taken = 0;
            foreach ($doctorCases as $case) {
                if ($taken >= self::MAX_MEDIA_CASES_PER_DOCTOR) break;

                // Don't disturb cases the user has actually uploaded media to.
                if ($case->media->isNotEmpty()) {
                    $taken++;
                    continue;
                }

                foreach (self::PHOTO_TILES as $tile => $sourceFile) {
                    [$rows, $bytes] = $this->upsertMedia(
                        $case->id,
                        'photograph',
                        $tile,
                        $photoDir . DIRECTORY_SEPARATOR . $sourceFile,
                        $disk
                    );
                    $mediaRows   += $rows;
                    $bytesCopied += $bytes;
                }
                foreach (self::XRAY_TILES as $tile => $sourceFile) {
                    [$rows, $bytes] = $this->upsertMedia(
                        $case->id,
                        'xray',
                        $tile,
                        $xrayDir . DIRECTORY_SEPARATOR . $sourceFile,
                        $disk
                    );
                    $mediaRows   += $rows;
                    $bytesCopied += $bytes;
                }

                $casesTouched++;
                $taken++;
            }
        }

        return [$mediaRows, $bytesCopied, $casesTouched];
    }

    /**
     * @return array{0: int, 1: int} — [rows added (0|1), bytes copied]
     */
    private function upsertMedia(int $caseId, string $section, string $tileId, string $sourceAbs, $disk): array
    {
        if (! File::exists($sourceAbs)) {
            return [0, 0];
        }

        // Deterministic destination filename keeps reseeding from spawning
        // duplicate files on disk.
        $ext = pathinfo($sourceAbs, PATHINFO_EXTENSION) ?: 'jpg';
        $relativeDir  = "case-media/{$caseId}/{$section}";
        $relativePath = "{$relativeDir}/seed-{$tileId}.{$ext}";

        $bytesCopied = 0;
        if (! $disk->exists($relativePath)) {
            $bytesCopied = (int) File::size($sourceAbs);
            $disk->put($relativePath, File::get($sourceAbs));
        }

        $existing = CaseMedia::where('case_id', $caseId)
            ->where('section', $section)
            ->where('tile_id', $tileId)
            ->first();

        if ($existing) {
            // Already present (from a previous seed run) — don't double-count.
            return [0, $bytesCopied];
        }

        CaseMedia::create([
            'case_id'       => $caseId,
            'section'       => $section,
            'tile_id'       => $tileId,
            'disk'          => 'public',
            'path'          => $relativePath,
            'mime_type'     => 'image/jpeg',
            'size_bytes'    => (int) File::size($sourceAbs),
            'original_name' => basename($sourceAbs),
            'crop_params'   => null,
        ]);

        return [1, $bytesCopied];
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) return "{$bytes} B";
        if ($bytes < 1024 * 1024) return number_format($bytes / 1024, 1) . ' KB';
        return number_format($bytes / 1024 / 1024, 2) . ' MB';
    }
}
