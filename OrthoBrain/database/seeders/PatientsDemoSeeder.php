<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeds a realistic patient roster per (doctor, practice). Without this every
 * doctor's "Search Patient" autocomplete returns 0 hits — the only way to
 * verify PR #69's persistence end-to-end was to manually create one patient
 * via the wizard.
 *
 * Idempotent: keyed by a deterministic email per (doctor, practice, slot)
 * so reseeding updates rather than duplicates.
 *
 * Run order: AFTER DoctorSeeder (needs doctors to exist).
 */
class PatientsDemoSeeder extends Seeder
{
    private const PATIENTS_PER_DOCTOR = 8;

    /**
     * 16 distinct first/last name pairs + chief complaints. Indexed
     * deterministically per doctor so each doctor gets their own slice.
     * Names span realistic adult + paediatric orthodontic demographics.
     */
    private const ROSTER = [
        ['Emma',     'Thompson',     '1992-03-14', 'Female',    'Crowding, aesthetic concern'],
        ['Marcus',   'Chen',         '2008-07-22', 'Male',      'Overjet correction'],
        ['Sofia',    'Ramirez',      '1985-11-03', 'Female',    'Open bite, speech issues'],
        ['Liam',     "O'Brien",      '2010-01-28', 'Male',      'Crossbite'],
        ['Aisha',    'Patel',        '1995-09-17', 'Female',    'Spacing, esthetic'],
        ['Jordan',   'Kim',          '1999-04-05', 'Non-binary','Deep bite'],
        ['Eleanor',  'Whitfield',    '1978-12-11', 'Female',    'Post-ortho relapse, mild crowding'],
        ['Diego',    'Fernandez',    '2012-06-30', 'Male',      'Phase 1 follow-up'],
        ['Priya',    'Nair',         '2001-02-19', 'Female',    'Class II, overbite'],
        ['Wesley',   'Harrington',   '1988-08-08', 'Male',      'Aesthetic improvement'],
        ['Hannah',   'Sorensen',     '2005-05-23', 'Female',    'Protrusion, crowding'],
        ['Tobias',   'Kowalski',     '1993-10-09', 'Male',      'Anterior open bite'],
        ['Zara',     'Hosseini',     '2003-04-12', 'Female',    'Severe rotation, lower anteriors'],
        ['Mateo',    'Cruz',         '1997-11-25', 'Male',      'Midline shift correction'],
        ['Yui',      'Tanaka',       '1990-06-04', 'Female',    'Mild Class III'],
        ['Felix',    'Andersson',    '2009-09-15', 'Male',      'Pre-orthognathic prep'],
    ];

    public function run(): void
    {
        $doctors = Doctor::whereNotNull('practice_id')->orderBy('id')->get();
        if ($doctors->isEmpty()) {
            $this->command?->warn('PatientsDemoSeeder: no doctors with a practice; run DoctorSeeder + CaseDemoSeeder first.');
            return;
        }

        $created = 0;
        $updated = 0;

        foreach ($doctors as $doctor) {
            // Spread the 16-name roster across doctors so they don't all get the
            // same first 8. Wraps around for doctors > 2.
            $offset = ($doctor->id - 1) * 4;
            for ($slot = 0; $slot < self::PATIENTS_PER_DOCTOR; $slot++) {
                [$first, $last, $dob, $gender, $complaint] = self::ROSTER[($offset + $slot) % count(self::ROSTER)];

                $email     = sprintf('patient-d%d-p%d-s%d@orthobrain.demo', $doctor->id, $doctor->practice_id, $slot);
                $chartId   = sprintf('PT-%d-%d-%02d', $doctor->id, $doctor->practice_id, $slot + 1);
                $phone     = sprintf('+1 (415) 555-0%03d', ($doctor->id * 100 + $slot) % 1000);

                $payload = [
                    'doctor_id'              => $doctor->id,
                    'practice_id'            => $doctor->practice_id,
                    'first_name'             => $first,
                    'last_name'              => $last,
                    'date_of_birth'          => Carbon::parse($dob),
                    'biological_gender'      => $gender,
                    'biological_gender_other'=> null,
                    'chart_id'               => $chartId,
                    'phone'                  => $phone,
                    'chief_complaint'        => $complaint,
                ];

                $existing = Patient::where('email', $email)->first();
                if ($existing) {
                    $existing->update($payload);
                    $updated++;
                } else {
                    Patient::create(array_merge($payload, ['email' => $email]));
                    $created++;
                }
            }
        }

        $this->command?->info(sprintf(
            'PatientsDemoSeeder: %d created, %d updated, %d total across %d doctors.',
            $created,
            $updated,
            Patient::count(),
            $doctors->count()
        ));
    }
}
