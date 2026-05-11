<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

/**
 * Gap-filling seeder for cases created by CaseDashboardSeeder.
 *
 * CaseDashboardSeeder creates case shells (status, timestamps, patient link,
 * bare prescription) but leaves six section-level fields empty. Without this
 * seeder the admin edit wizard shows blank Impressions, Shipping, Additional
 * Info, and Submit Order sections — making it impossible to verify B-2
 * read/write parity on a fresh DB.
 *
 * Fills:
 *   cases.scanner_id + impression_method   — Impressions section
 *   cases.submitter_initials               — Submit Order section (non-DRAFT)
 *   cases.rejection_reason                 — free-text on REJECTED cases
 *   case_shipping_addresses                — Shipping section
 *   case_additional_info                   — Additional Info section
 *
 * Idempotent: DB::update and updateOrInsert are keyed by case_id so
 * rerunning produces no duplicates.
 *
 * Run order: after CaseDashboardSeeder.
 */
class CaseSectionDataSeeder extends Seeder
{
    /** Scanner IDs 1-10 — original set, before CaseDemoSeeder added duplicates 11-16. */
    private const SCANNER_IDS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

    /**
     * Weighted toward DIGITAL (3:1) — reflects real-world clinic mix.
     * `cases.impression_method` is an ENUM('DIGITAL','PHYSICAL').
     */
    private const IMPRESSION_METHODS = ['DIGITAL', 'DIGITAL', 'DIGITAL', 'PHYSICAL'];

    /**
     * Representative US address combos from the geo seed.
     * case_shipping_addresses stores FK IDs, not text labels.
     * country_id = 1 (United States) for all rows.
     */
    private const SHIPPING_LOCATIONS = [
        ['zip_id' =>  1, 'city_id' => 1, 'state_id' =>  1],  // Birmingham, AL 35201
        ['zip_id' => 13, 'city_id' => 5, 'state_id' =>  2],  // Anchorage, AK 99501
        ['zip_id' => 20, 'city_id' => 8, 'state_id' =>  3],  // Phoenix, AZ 85001
        ['zip_id' =>  4, 'city_id' => 2, 'state_id' =>  1],  // Montgomery, AL 36101
        ['zip_id' => 16, 'city_id' => 6, 'state_id' =>  2],  // Fairbanks, AK 99701
        ['zip_id' =>  7, 'city_id' => 3, 'state_id' =>  1],  // Huntsville, AL 35801
    ];

    /** Free-text rejection reasons — stored directly on cases.rejection_reason. */
    private const REJECTION_REASONS = [
        'Insufficient photograph quality — please retake frontal and lateral photos in good lighting.',
        'X-ray images are missing or incomplete. Full series required before we can proceed.',
        'Prescription details are incomplete. Please specify arch treatment preference.',
        'Scan files appear corrupted or are the wrong format. Please re-upload.',
        'Patient records do not match the submitted case. Please verify patient identity.',
    ];

    /**
     * Three realistic case_additional_info.data payloads:
     *   0 — WNL (everything within normal limits, minimal complexity)
     *   1 — Mild history flags (crowding, previous extractions, bruxing)
     *   2 — Complex case (implants, prior braces, parafunctional habits)
     */
    private const ADDITIONAL_INFO_VARIANTS = [
        '{"diagnosis":[],"medicalHistory":{"wnl":true},"dentalHistory":{"wnl":true},"orthodonticHistory":{"noPreviousTreatment":true},"familyHistory":{"none":true},"parafunctionalHabits":{"none":true}}',
        '{"diagnosis":["crowding","spacing"],"medicalHistory":{"wnl":true},"dentalHistory":{"previousOrthodonticTreatment":true,"previousExtractions":true},"orthodonticHistory":{"noPreviousTreatment":false},"familyHistory":{"relativeHadOrthodonticExtractions":true},"parafunctionalHabits":{"bruxing":true}}',
        '{"diagnosis":["crowding","deepBite","midlineDeviation"],"medicalHistory":{"medications":true,"medicationsList":"Lisinopril 10mg daily"},"dentalHistory":{"wnl":false,"implants":true},"orthodonticHistory":{"noPreviousTreatment":false,"previousBraces":true},"familyHistory":{"none":true},"parafunctionalHabits":{"clenching":true,"tongueThrusing":true}}',
    ];

    public function run(): void
    {
        $faker = Faker::create();
        $now   = now();

        // Target only cases created by CaseDashboardSeeder.
        // Their case_code follows the pattern D{doctor_id}-{STATUS_LETTER}-{NNN},
        // e.g. D1-D-001, D12-S-006. The REGEXP anchors on the 'D' prefix + digits.
        $cases = DB::table('cases')
            ->whereRaw("case_code REGEXP '^D[0-9]+-[A-Z]-[0-9]+'")
            ->orderBy('id')
            ->get();

        $this->command?->info("CaseSectionDataSeeder: filling section data for {$cases->count()} cases...");

        foreach ($cases as $case) {
            $idx = $case->id;   // deterministic index for all modulo picks

            $scanner  = self::SCANNER_IDS[$idx % count(self::SCANNER_IDS)];
            $method   = self::IMPRESSION_METHODS[$idx % count(self::IMPRESSION_METHODS)];
            $loc      = self::SHIPPING_LOCATIONS[$idx % count(self::SHIPPING_LOCATIONS)];
            $aiJson   = self::ADDITIONAL_INFO_VARIANTS[$idx % count(self::ADDITIONAL_INFO_VARIANTS)];

            // 1 — Impressions: scanner_id + impression_method
            DB::table('cases')->where('id', $case->id)->update([
                'scanner_id'        => $scanner,
                'impression_method' => $method,
                'updated_at'        => $now,
            ]);

            // 2 — Submit Order: submitter_initials (non-DRAFT only)
            if (in_array($case->status, ['SUBMITTED', 'IN_REVIEW', 'APPROVED', 'REJECTED'], true)) {
                $initials = strtoupper(
                    chr(65 + ($case->doctor_id % 26)) .
                    chr(65 + (($case->doctor_id + $case->id) % 26))
                );
                DB::table('cases')->where('id', $case->id)->update([
                    'submitter_initials' => $initials,
                    'updated_at'         => $now,
                ]);
            }

            // 3 — Rejection reason (REJECTED only)
            if ($case->status === 'REJECTED') {
                DB::table('cases')->where('id', $case->id)->update([
                    'rejection_reason' => self::REJECTION_REASONS[$idx % count(self::REJECTION_REASONS)],
                    'updated_at'       => $now,
                ]);
            }

            // 4 — Shipping address (all cases — admin can edit any status)
            DB::table('case_shipping_addresses')->updateOrInsert(
                ['case_id' => $case->id],
                [
                    'practice_name'    => $faker->company() . ' Dental',
                    'doctor_name'      => 'Dr. ' . $faker->lastName(),
                    'street_address_1' => $faker->buildingNumber() . ' ' . $faker->streetName(),
                    'street_address_2' => null,
                    'zip_id'           => $loc['zip_id'],
                    'city_id'          => $loc['city_id'],
                    'state_id'         => $loc['state_id'],
                    'country_id'       => 1,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]
            );

            // 5 — Additional info JSON
            DB::table('case_additional_info')->updateOrInsert(
                ['case_id' => $case->id],
                [
                    'data'       => $aiJson,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $this->command?->info('CaseSectionDataSeeder complete.');
    }
}
