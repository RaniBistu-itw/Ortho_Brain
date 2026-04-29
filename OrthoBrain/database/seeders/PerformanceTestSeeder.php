<?php

namespace Database\Seeders;

use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PerformanceTestSeeder extends Seeder
{
    private const COUNTRIES         = 250;
    private const STATES            = 5_000;
    // CITIES (~50k), ZIPCODES (~200k), CASES (~25k) are produced by chunked
    // generation with random per-parent counts — see comments in each method.
    private const PRODUCT_CATS      = 50;
    private const PRODUCT_SUBCATS   = 200;
    private const PRODUCTS          = 100;
    private const SCANNERS          = 30;
    private const PRACTICES         = 500;
    private const DOCTORS           = 2_000;

    private const CHUNK             = 1_000;
    private const SMALL_CHUNK       = 100;

    private const PARENT_CHUNK      = 1_000;
    private const DOCTOR_PARENT_CHUNK = 500;

    private const USER_EMAIL_TAG    = '+perfseed@orthobrain.test';

    private \Faker\Generator $faker;

    public function run(): void
    {
        $this->faker = FakerFactory::create();
        $this->faker->seed(20260429);

        DB::connection()->disableQueryLog();

        $now = now();
        $this->command->info('PerformanceTestSeeder starting at ' . $now->toDateTimeString());

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            $this->truncateAll();

            $countryIds = $this->seedCountries($now);
            $this->seedStates($countryIds, $now);
            $this->seedCities($now);
            $this->seedZipcodes($now);

            $catIds    = $this->seedProductCategories($now);
            $subcatIds = $this->seedProductSubcategories($catIds, $now);
            $this->seedProducts($catIds, $subcatIds, $now);
            $this->seedScanners($now);

            $practiceIds = $this->seedPractices($now);

            $doctorPracticeMap = $this->seedDoctorsWithUsers($practiceIds, $now);
            $this->backfillPracticeOwners($doctorPracticeMap);
            unset($doctorPracticeMap);
            gc_collect_cycles();

            $this->seedCases();

            unset($countryIds, $catIds, $subcatIds, $practiceIds);
            gc_collect_cycles();
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->command->info('PerformanceTestSeeder finished at ' . now()->toDateTimeString());
    }

    /* ---------------------------------------------------------------------
     | Truncate
     |-------------------------------------------------------------------- */

    private function truncateAll(): void
    {
        $this->logMem(__FUNCTION__);
        $this->command->info('Truncating target tables (FK checks already disabled)...');

        // Reverse FK order. Users only the doctor seed-rows (tagged email).
        DB::table('cases')->truncate();
        DB::table('doctors')->truncate();
        DB::table('practices')->truncate();
        DB::table('scanners')->truncate();
        DB::table('products')->truncate();
        DB::table('products_subcategory')->truncate();
        DB::table('products_category')->truncate();
        DB::table('zipcodes')->truncate();
        DB::table('cities')->truncate();
        DB::table('states')->truncate();
        DB::table('countries')->truncate();

        DB::table('users')
            ->where('email', 'like', '%' . self::USER_EMAIL_TAG)
            ->delete();
    }

    /* ---------------------------------------------------------------------
     | Countries  (small — 250 ids returned for state seeding)
     |-------------------------------------------------------------------- */

    private function seedCountries(\Carbon\Carbon $now): array
    {
        $this->logMem(__FUNCTION__);
        $this->command->info('Seeding countries...');

        $countries = $this->countryDataset();
        while (count($countries) < self::COUNTRIES) {
            $i    = count($countries) + 1;
            $code = 'X' . str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $countries[] = ['name' => "Test Country {$i}", 'country_code' => $code, 'phone_code' => '+999'];
        }
        $countries = array_slice($countries, 0, self::COUNTRIES);

        $rows = [];
        foreach ($countries as $c) {
            $rows[] = [
                'name'         => $c['name'],
                'country_code' => $c['country_code'],
                'phone_code'   => $c['phone_code'],
                'status'       => $this->weightedStatus(),
                'created_at'   => $now,
                'updated_at'   => $now,
                'deleted_at'   => null,
            ];
        }

        $this->insertChunked('countries', $rows, self::SMALL_CHUNK);
        $ids = DB::table('countries')->orderBy('id')->pluck('id')->all();
        unset($rows, $countries);
        gc_collect_cycles();

        return $ids;
    }

    /* ---------------------------------------------------------------------
     | States  (5k rows, no return — children chunk over states directly)
     |-------------------------------------------------------------------- */

    private function seedStates(array $countryIds, \Carbon\Carbon $now): void
    {
        $this->logMem(__FUNCTION__);
        $this->command->info('Seeding states...');

        // Weighted distribution: first ~20 countries get many states, rest fewer.
        $weights = [];
        foreach ($countryIds as $idx => $cid) {
            $weights[$cid] = $idx < 20 ? 50 : ($idx < 80 ? 15 : 5);
        }
        $sum        = array_sum($weights);
        $perCountry = [];
        foreach ($weights as $cid => $w) {
            $perCountry[$cid] = max(1, (int) round(self::STATES * $w / $sum));
        }
        $diff = self::STATES - array_sum($perCountry);
        $keys = array_keys($perCountry);
        for ($i = 0; $diff !== 0; $i = ($i + 1) % count($keys)) {
            $cid = $keys[$i];
            if ($diff > 0)                       { $perCountry[$cid]++; $diff--; }
            elseif ($perCountry[$cid] > 1)       { $perCountry[$cid]--; $diff++; }
        }

        $rows  = [];
        $total = 0;
        foreach ($perCountry as $cid => $count) {
            for ($i = 1; $i <= $count; $i++) {
                $code   = 'S' . $cid . '-' . $i;
                $rows[] = [
                    'country_id' => $cid,
                    'name'       => $this->faker->state() . ' ' . $i,
                    'state_code' => substr($code, 0, 100),
                    'status'     => $this->weightedStatus(),
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ];
                if (count($rows) >= self::CHUNK) {
                    DB::table('states')->insert($rows);
                    $total += count($rows);
                    $rows = [];
                }
            }
        }
        if (! empty($rows)) {
            DB::table('states')->insert($rows);
            $total += count($rows);
        }

        unset($rows, $weights, $perCountry);
        gc_collect_cycles();
        $this->command->info(sprintf(
            '[GC] states done: total %d, mem %s MB',
            $total,
            round(memory_get_usage(true) / 1024 / 1024, 2)
        ));
    }

    /* ---------------------------------------------------------------------
     | Cities  (chunkById over states; ~10 cities per state)
     |-------------------------------------------------------------------- */

    private function seedCities(\Carbon\Carbon $now): void
    {
        $this->logMem(__FUNCTION__);
        $this->command->info('Seeding cities...');

        $rows       = [];
        $total      = 0;
        $chunkCount = 0;

        DB::table('states')
            ->select('id')
            ->orderBy('id')
            ->chunkById(self::PARENT_CHUNK, function ($states) use (&$rows, &$total, &$chunkCount, $now) {
                foreach ($states as $state) {
                    $n = random_int(8, 12); // ~10 avg × 5,000 ≈ 50,000
                    for ($i = 0; $i < $n; $i++) {
                        $rows[] = [
                            'state_id'   => $state->id,
                            'name'       => $this->faker->city(),
                            'status'     => $this->weightedStatus(),
                            'created_at' => $now,
                            'updated_at' => $now,
                            'deleted_at' => null,
                        ];
                        if (count($rows) >= self::CHUNK) {
                            DB::table('cities')->insert($rows);
                            $total += count($rows);
                            $rows = [];
                        }
                    }
                }
                $chunkCount++;
                if ($chunkCount % 5 === 0) {
                    gc_collect_cycles();
                    $this->command->info(sprintf(
                        '  cities: %d (mem %s MB)',
                        $total,
                        round(memory_get_usage(true) / 1024 / 1024, 2)
                    ));
                }
            }, 'id');

        if (! empty($rows)) {
            DB::table('cities')->insert($rows);
            $total += count($rows);
            $rows = [];
        }

        unset($rows);
        gc_collect_cycles();
        $this->command->info(sprintf(
            '[GC] cities done: total %d, mem %s MB',
            $total,
            round(memory_get_usage(true) / 1024 / 1024, 2)
        ));
    }

    /* ---------------------------------------------------------------------
     | Zipcodes  (chunkById over cities; ~4 zipcodes per city)
     |-------------------------------------------------------------------- */

    private function seedZipcodes(\Carbon\Carbon $now): void
    {
        $this->logMem(__FUNCTION__);
        $this->command->info('Seeding zipcodes...');

        $rows       = [];
        $total      = 0;
        $chunkCount = 0;

        DB::table('cities')
            ->select('id')
            ->orderBy('id')
            ->chunkById(self::PARENT_CHUNK, function ($cities) use (&$rows, &$total, &$chunkCount, $now) {
                foreach ($cities as $city) {
                    $n = random_int(3, 5); // ~4 avg × 50,000 ≈ 200,000
                    for ($i = 0; $i < $n; $i++) {
                        $rows[] = [
                            'code'       => str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT),
                            'city_id'    => $city->id,
                            'status'     => $this->weightedStatus(),
                            'details'    => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                            'deleted_at' => null,
                        ];
                        if (count($rows) >= self::CHUNK) {
                            DB::table('zipcodes')->insert($rows);
                            $total += count($rows);
                            $rows = [];
                        }
                    }
                }
                $chunkCount++;
                if ($chunkCount % 10 === 0) {
                    gc_collect_cycles();
                    $this->command->info(sprintf(
                        '  zipcodes: %d (mem %s MB)',
                        $total,
                        round(memory_get_usage(true) / 1024 / 1024, 2)
                    ));
                }
            }, 'id');

        if (! empty($rows)) {
            DB::table('zipcodes')->insert($rows);
            $total += count($rows);
            $rows = [];
        }

        unset($rows);
        gc_collect_cycles();
        $this->command->info(sprintf(
            '[GC] zipcodes done: total %d, mem %s MB',
            $total,
            round(memory_get_usage(true) / 1024 / 1024, 2)
        ));
    }

    /* ---------------------------------------------------------------------
     | Product Categories / Subcategories / Products  (small)
     |-------------------------------------------------------------------- */

    private function seedProductCategories(\Carbon\Carbon $now): array
    {
        $this->logMem(__FUNCTION__);
        $this->command->info('Seeding product categories...');
        $names = $this->categoryNames(self::PRODUCT_CATS);
        $rows  = [];
        foreach ($names as $name) {
            $rows[] = [
                'name'       => $name,
                'status'     => $this->faker->boolean(90) ? 'ACTIVE' : 'INACTIVE',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ];
        }
        $this->insertChunked('products_category', $rows, self::SMALL_CHUNK);
        return DB::table('products_category')->orderBy('id')->pluck('id')->all();
    }

    private function seedProductSubcategories(array $catIds, \Carbon\Carbon $now): array
    {
        $this->logMem(__FUNCTION__);
        $this->command->info('Seeding product subcategories...');
        $rows = [];
        for ($i = 0; $i < self::PRODUCT_SUBCATS; $i++) {
            $rows[] = [
                'category_id' => $catIds[array_rand($catIds)],
                'name'        => $this->subcategoryName($i),
                'description' => $this->faker->optional()->sentence(8),
                'status'      => $this->faker->boolean(90),
                'created_at'  => $now,
                'updated_at'  => $now,
                'deleted_at'  => null,
            ];
        }
        $this->insertChunked('products_subcategory', $rows, self::SMALL_CHUNK);
        return DB::table('products_subcategory')->orderBy('id')->pluck('id')->all();
    }

    private function seedProducts(array $catIds, array $subcatIds, \Carbon\Carbon $now): void
    {
        $this->logMem(__FUNCTION__);
        $this->command->info('Seeding products...');
        $rows = [];
        for ($i = 0; $i < self::PRODUCTS; $i++) {
            $catId    = $catIds[array_rand($catIds)];
            $subId    = $subcatIds[array_rand($subcatIds)];
            $fromStep = random_int(1, 10);
            $rows[]   = [
                'name'                => $this->productName($i),
                'category_id'         => $catId,
                'subcategory_id'      => $subId,
                'base_price'          => $this->faker->randomFloat(2, 99, 4999),
                'number_of_revisions' => random_int(1, 5),
                'product_term_months' => random_int(6, 36),
                'from_step'           => $fromStep,
                'to_step'             => $fromStep + random_int(5, 30),
                'url'                 => null,
                'status'              => $this->weightedStatus(),
                'description'         => $this->faker->paragraph(2),
                'created_at'          => $now,
                'updated_at'          => $now,
                'deleted_at'          => null,
            ];
        }
        $this->insertChunked('products', $rows, self::SMALL_CHUNK);
    }

    /* ---------------------------------------------------------------------
     | Scanners
     |-------------------------------------------------------------------- */

    private function seedScanners(\Carbon\Carbon $now): void
    {
        $this->logMem(__FUNCTION__);
        $this->command->info('Seeding scanners...');
        $names = $this->scannerNames();
        for ($i = count($names); $i < self::SCANNERS; $i++) {
            $names[] = 'TestScanner-' . ($i + 1);
        }
        $names = array_slice($names, 0, self::SCANNERS);

        $rows = [];
        foreach ($names as $name) {
            $rows[] = [
                'name'            => $name,
                'description'     => $this->faker->optional()->sentence(6),
                'portal_password' => null,
                'portal_link'     => null,
                'status'          => $this->weightedStatus(),
                'created_at'      => $now,
                'updated_at'      => $now,
                'deleted_at'      => null,
            ];
        }
        $this->insertChunked('scanners', $rows, self::SMALL_CHUNK);
    }

    /* ---------------------------------------------------------------------
     | Practices  (self-fetches small city/zip pools — no large param arrays)
     |-------------------------------------------------------------------- */

    private function seedPractices(\Carbon\Carbon $now): array
    {
        $this->logMem(__FUNCTION__);
        $this->command->info('Seeding practices...');

        // Geographic clustering: 70% from a "cluster" pool (cities in the
        // bottom 5% of id range), 30% from anywhere. Pools are bounded at
        // PRACTICES rows (500), so memory stays small regardless of total
        // city count.
        $maxCityId    = (int) DB::table('cities')->max('id');
        $clusterMaxId = max(1, (int) ceil($maxCityId * 0.05));

        $clusterPool = DB::table('cities')
            ->join('states', 'cities.state_id', '=', 'states.id')
            ->where('cities.id', '<=', $clusterMaxId)
            ->select(['cities.id as city_id', 'cities.state_id', 'states.country_id'])
            ->inRandomOrder()
            ->limit(self::PRACTICES)
            ->get()
            ->all();

        $randomPool = DB::table('cities')
            ->join('states', 'cities.state_id', '=', 'states.id')
            ->select(['cities.id as city_id', 'cities.state_id', 'states.country_id'])
            ->inRandomOrder()
            ->limit(self::PRACTICES)
            ->get()
            ->all();

        $zipPool = DB::table('zipcodes')
            ->inRandomOrder()
            ->limit(self::PRACTICES)
            ->pluck('id')
            ->all();

        $rows  = [];
        $total = 0;
        for ($i = 0; $i < self::PRACTICES; $i++) {
            $useCluster = $this->faker->boolean(70) && ! empty($clusterPool);
            $row        = $useCluster
                ? $clusterPool[array_rand($clusterPool)]
                : $randomPool[array_rand($randomPool)];

            $rows[] = [
                'owner_id'           => null,
                'name'               => $this->practiceName($i),
                'website'            => 'https://' . Str::slug($this->faker->company()) . '.example',
                'logo_path'          => null,
                'phone_country_code' => '+1',
                'phone_number'       => $this->faker->numerify('##########'),
                'street_address_1'   => $this->faker->streetAddress(),
                'street_address_2'   => $this->faker->optional(0.2)->secondaryAddress(),
                'zip_id'             => $zipPool[array_rand($zipPool)],
                'city_id'            => $row->city_id,
                'state_id'           => $row->state_id,
                'country_id'         => $row->country_id,
                'status'             => $this->weightedStatus(),
                'created_at'         => $now,
                'updated_at'         => $now,
                'deleted_at'         => null,
            ];

            if (count($rows) >= self::SMALL_CHUNK) {
                DB::table('practices')->insert($rows);
                $total += count($rows);
                $rows = [];
            }
        }
        if (! empty($rows)) {
            DB::table('practices')->insert($rows);
            $total += count($rows);
        }

        $ids = DB::table('practices')->orderBy('id')->pluck('id')->all();
        unset($rows, $clusterPool, $randomPool, $zipPool);
        gc_collect_cycles();
        $this->command->info(sprintf(
            '[GC] practices done: total %d, mem %s MB',
            $total,
            round(memory_get_usage(true) / 1024 / 1024, 2)
        ));

        return $ids;
    }

    /* ---------------------------------------------------------------------
     | Doctors (with users) — returns practice_id => first_doctor_id map
     | for the practice owner backfill. Doctor IDs are NOT returned —
     | seedCases chunks over the doctors table directly.
     |-------------------------------------------------------------------- */

    private function seedDoctorsWithUsers(array $practiceIds, \Carbon\Carbon $now): array
    {
        $this->logMem(__FUNCTION__);
        $this->command->info('Seeding users + doctors...');

        $doctorEnums = [
            'preferred_contact_mode'           => ['DOCTOR_ONLY', 'EMPLOYEE_OFFICE', 'DOCTOR_AND_EMPLOYEE_OFFICE'],
            'preferred_tooth_numbering_system' => ['UNIVERSAL', 'FDI', 'PALMER', 'INTERNATIONAL'],
            'smile_arc_pref'                   => ['DEFER', 'LATERALS_0_5MM_SHORTER', 'LATERALS_SAME_LENGTH'],
            'small_lateral_incisors_pref'      => ['DEFER', 'IPR_LOWER_CAMOUFLAGE', 'LEAVE_SPACING_MESIAL_DISTAL'],
            'mixed_dentition_pref'             => ['DEFER', 'NO_APPLIANCES'],
            'orthodontic_extractions_pref'     => ['DEFER', 'NO_EXTRACTIONS'],
            'ipr_protocol_pref'                => ['DEFER', 'NO_IPR', 'OTHER'],
            'elastics_bonded_buttons_pref'     => ['YES', 'NO'],
            'extractions_if_suggested_pref'    => ['YES', 'NO'],
            'attachment_stage_pref'            => ['AT_STEP_1', 'AT_STEP_OTHER'],
        ];

        // Distribute doctors across practices: 1–5 per practice, fill until 2,000.
        $doctorPerPractice = [];
        $remaining         = self::DOCTORS;
        foreach ($practiceIds as $pid) {
            if ($remaining <= 0) { break; }
            $n = min(random_int(1, 5), $remaining);
            $doctorPerPractice[$pid] = $n;
            $remaining -= $n;
        }
        while ($remaining > 0) {
            $pid = $practiceIds[array_rand($practiceIds)];
            $doctorPerPractice[$pid] = ($doctorPerPractice[$pid] ?? 0) + 1;
            $remaining--;
        }

        $passwordHash      = Hash::make('password');
        $globalIdx         = 0;
        $totalDoctors      = 0;
        $doctorPracticeMap = [];

        $chunkUsers = [];
        $chunkMeta  = [];
        $insertChunk = function () use (&$chunkUsers, &$chunkMeta, &$totalDoctors, &$doctorPracticeMap) {
            if (empty($chunkUsers)) { return; }

            DB::table('users')->insert($chunkUsers);

            $emails    = array_column($chunkUsers, 'email');
            $emailToId = DB::table('users')
                ->whereIn('email', $emails)
                ->pluck('id', 'email')
                ->all();

            $doctorRows = [];
            foreach ($chunkUsers as $i => $u) {
                $meta         = $chunkMeta[$i];
                $userId       = $emailToId[$u['email']];
                $doctorRows[] = $meta['doctor'] + ['user_id' => $userId];
            }

            DB::table('doctors')->insert($doctorRows);

            $userIds      = array_column($doctorRows, 'user_id');
            $userToDoctor = DB::table('doctors')
                ->whereIn('user_id', $userIds)
                ->pluck('id', 'user_id')
                ->all();

            foreach ($chunkMeta as $i => $meta) {
                $userId   = $emailToId[$chunkUsers[$i]['email']];
                $doctorId = $userToDoctor[$userId];
                $totalDoctors++;
                $pid = $meta['practice_id'];
                if ($pid !== null && ! isset($doctorPracticeMap[$pid])) {
                    $doctorPracticeMap[$pid] = $doctorId;
                }
            }

            unset($emailToId, $userToDoctor, $doctorRows);
            $chunkUsers = [];
            $chunkMeta  = [];
        };

        foreach ($doctorPerPractice as $pid => $count) {
            for ($k = 0; $k < $count; $k++) {
                $globalIdx++;
                $first = $this->faker->firstName();
                $last  = $this->faker->lastName();
                $email = strtolower($first . '.' . $last . $globalIdx) . self::USER_EMAIL_TAG;

                $chunkUsers[] = [
                    'email'         => $email,
                    'password_hash' => $passwordHash,
                    'role'          => 'DOCTOR',
                    'is_active'     => 1,
                    'last_login_at' => null,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                    'deleted_at'    => null,
                ];

                $approvalStatus = $this->faker->randomElement(['PENDING', 'APPROVED', 'REJECTED', 'SUSPENDED']);
                $chunkMeta[]    = [
                    'practice_id' => $pid,
                    'doctor'      => [
                        'practice_id'                       => $pid,
                        'first_name'                        => $first,
                        'last_name'                         => $last,
                        'profile_photo_s3_key'              => null,
                        'preferred_language'                => $this->faker->randomElement(['English', 'Spanish', 'French', 'Mandarin']),
                        'currently_providing_ortho_services'=> $this->faker->boolean(80) ? 1 : 0,
                        'preferred_contact_mode'            => $this->faker->randomElement($doctorEnums['preferred_contact_mode']),
                        'doctor_contact_email'              => strtolower($first . '.' . $last . $globalIdx) . '@orthobrain.test',
                        'doctor_cell_phone'                 => $this->faker->numerify('##########'),
                        'other_email'                       => null,
                        'preferred_tooth_numbering_system'  => $this->faker->randomElement($doctorEnums['preferred_tooth_numbering_system']),
                        'smile_arc_pref'                    => $this->faker->randomElement($doctorEnums['smile_arc_pref']),
                        'small_lateral_incisors_pref'       => $this->faker->randomElement($doctorEnums['small_lateral_incisors_pref']),
                        'mixed_dentition_pref'              => $this->faker->randomElement($doctorEnums['mixed_dentition_pref']),
                        'orthodontic_extractions_pref'      => $this->faker->randomElement($doctorEnums['orthodontic_extractions_pref']),
                        'ipr_protocol_pref'                 => $this->faker->randomElement($doctorEnums['ipr_protocol_pref']),
                        'ipr_protocol_other_note'           => null,
                        'elastics_bonded_buttons_pref'      => $this->faker->randomElement($doctorEnums['elastics_bonded_buttons_pref']),
                        'extractions_if_suggested_pref'     => $this->faker->randomElement($doctorEnums['extractions_if_suggested_pref']),
                        'attachment_stage_pref'             => $this->faker->randomElement($doctorEnums['attachment_stage_pref']),
                        'approval_status'                   => $approvalStatus,
                        'approved_at'                       => $approvalStatus === 'APPROVED' ? $now : null,
                        'approved_by_admin_id'              => null,
                        'rejection_reason'                  => $approvalStatus === 'REJECTED' ? 'Auto-generated' : null,
                        'created_at'                        => $now,
                        'updated_at'                        => $now,
                        'deleted_at'                        => null,
                    ],
                ];

                if (count($chunkUsers) >= self::CHUNK) {
                    $insertChunk();
                    gc_collect_cycles();
                    $this->command->info(sprintf(
                        '  doctors: %d (mem %s MB)',
                        $totalDoctors,
                        round(memory_get_usage(true) / 1024 / 1024, 2)
                    ));
                }
            }
        }
        $insertChunk();

        unset($doctorPerPractice, $chunkUsers, $chunkMeta);
        gc_collect_cycles();
        $this->command->info(sprintf(
            '[GC] doctors done: total %d, mem %s MB',
            $totalDoctors,
            round(memory_get_usage(true) / 1024 / 1024, 2)
        ));

        return $doctorPracticeMap;
    }

    private function backfillPracticeOwners(array $doctorPracticeMap): void
    {
        $this->logMem(__FUNCTION__);
        $this->command->info('Backfilling practice owners...');
        // 500 small UPDATEs — trivial.
        foreach ($doctorPracticeMap as $pid => $did) {
            DB::table('practices')->where('id', $pid)->update(['owner_id' => $did]);
        }
    }

    /* ---------------------------------------------------------------------
     | Cases  (chunkById over doctors; ~12.5 cases per doctor)
     |-------------------------------------------------------------------- */

    private function seedCases(): void
    {
        $this->logMem(__FUNCTION__);
        $this->command->info('Seeding cases...');

        $statusBuckets = [
            ['DRAFT', 10],
            ['SUBMITTED', 20],
            ['IN_REVIEW', 25],
            ['APPROVED', 35],
            ['REJECTED', 10],
        ];
        $statusPool = [];
        foreach ($statusBuckets as [$s, $w]) {
            for ($i = 0; $i < $w; $i++) { $statusPool[] = $s; }
        }

        $rows        = [];
        $total       = 0;
        $caseCounter = 0;
        $chunkCount  = 0;

        DB::table('doctors')
            ->select(['id', 'practice_id'])
            ->orderBy('id')
            ->chunkById(self::DOCTOR_PARENT_CHUNK, function ($doctors) use (&$rows, &$total, &$caseCounter, &$chunkCount, $statusPool) {
                foreach ($doctors as $doctor) {
                    $n = random_int(10, 15); // ~12.5 avg × 2,000 ≈ 25,000
                    for ($i = 0; $i < $n; $i++) {
                        $caseCounter++;
                        $status = $statusPool[array_rand($statusPool)];

                        $r = $this->faker->numberBetween(1, 100);
                        if ($r <= 60)      { $createdAt = $this->faker->dateTimeBetween('-6 months', 'now'); }
                        elseif ($r <= 90)  { $createdAt = $this->faker->dateTimeBetween('-18 months', '-6 months'); }
                        else               { $createdAt = $this->faker->dateTimeBetween('-24 months', '-18 months'); }

                        $submittedAt = in_array($status, ['SUBMITTED', 'IN_REVIEW', 'APPROVED', 'REJECTED'], true)
                            ? $createdAt
                            : null;

                        $rows[] = [
                            'doctor_id'    => $doctor->id,
                            'practice_id'  => $doctor->practice_id,
                            'patient_id'   => null,
                            'case_code'    => 'CASE-' . str_pad((string) $caseCounter, 7, '0', STR_PAD_LEFT),
                            'status'       => $status,
                            'submitted_at' => $submittedAt,
                            'created_at'   => $createdAt,
                            'updated_at'   => $createdAt,
                            'deleted_at'   => null,
                        ];

                        if (count($rows) >= self::CHUNK) {
                            DB::table('cases')->insert($rows);
                            $total += count($rows);
                            $rows = [];
                        }
                    }
                }
                $chunkCount++;
                if ($chunkCount % 2 === 0) {
                    gc_collect_cycles();
                    $this->command->info(sprintf(
                        '  cases: %d (mem %s MB)',
                        $total,
                        round(memory_get_usage(true) / 1024 / 1024, 2)
                    ));
                }
            }, 'id');

        if (! empty($rows)) {
            DB::table('cases')->insert($rows);
            $total += count($rows);
        }

        unset($rows, $statusPool);
        gc_collect_cycles();
        $this->command->info(sprintf(
            '[GC] cases done: total %d, mem %s MB',
            $total,
            round(memory_get_usage(true) / 1024 / 1024, 2)
        ));
    }

    /* ---------------------------------------------------------------------
     | Helpers
     |-------------------------------------------------------------------- */

    private function logMem(string $label): void
    {
        $this->command->info(sprintf(
            '[Memory] %s MB before %s',
            round(memory_get_usage(true) / 1024 / 1024, 2),
            $label
        ));
    }

    private function insertChunked(string $table, array $rows, int $chunk): void
    {
        if (empty($rows)) { return; }
        foreach (array_chunk($rows, $chunk) as $batch) {
            DB::table($table)->insert($batch);
        }
    }

    private function weightedStatus(): string
    {
        return $this->faker->boolean(90) ? 'ACTIVE' : 'INACTIVE';
    }

    /* ---------------------------------------------------------------------
     | Realistic name pools
     |-------------------------------------------------------------------- */

    private function countryDataset(): array
    {
        return [
            ['name' => 'United States',        'country_code' => 'US', 'phone_code' => '+1'],
            ['name' => 'Canada',               'country_code' => 'CA', 'phone_code' => '+1'],
            ['name' => 'United Kingdom',       'country_code' => 'GB', 'phone_code' => '+44'],
            ['name' => 'Australia',            'country_code' => 'AU', 'phone_code' => '+61'],
            ['name' => 'Germany',              'country_code' => 'DE', 'phone_code' => '+49'],
            ['name' => 'France',               'country_code' => 'FR', 'phone_code' => '+33'],
            ['name' => 'Italy',                'country_code' => 'IT', 'phone_code' => '+39'],
            ['name' => 'Spain',                'country_code' => 'ES', 'phone_code' => '+34'],
            ['name' => 'Netherlands',          'country_code' => 'NL', 'phone_code' => '+31'],
            ['name' => 'Belgium',              'country_code' => 'BE', 'phone_code' => '+32'],
            ['name' => 'Switzerland',          'country_code' => 'CH', 'phone_code' => '+41'],
            ['name' => 'Austria',              'country_code' => 'AT', 'phone_code' => '+43'],
            ['name' => 'Ireland',              'country_code' => 'IE', 'phone_code' => '+353'],
            ['name' => 'Portugal',             'country_code' => 'PT', 'phone_code' => '+351'],
            ['name' => 'Sweden',               'country_code' => 'SE', 'phone_code' => '+46'],
            ['name' => 'Norway',               'country_code' => 'NO', 'phone_code' => '+47'],
            ['name' => 'Denmark',              'country_code' => 'DK', 'phone_code' => '+45'],
            ['name' => 'Finland',              'country_code' => 'FI', 'phone_code' => '+358'],
            ['name' => 'Iceland',              'country_code' => 'IS', 'phone_code' => '+354'],
            ['name' => 'Poland',               'country_code' => 'PL', 'phone_code' => '+48'],
            ['name' => 'Czech Republic',       'country_code' => 'CZ', 'phone_code' => '+420'],
            ['name' => 'Slovakia',             'country_code' => 'SK', 'phone_code' => '+421'],
            ['name' => 'Hungary',              'country_code' => 'HU', 'phone_code' => '+36'],
            ['name' => 'Romania',              'country_code' => 'RO', 'phone_code' => '+40'],
            ['name' => 'Bulgaria',             'country_code' => 'BG', 'phone_code' => '+359'],
            ['name' => 'Greece',               'country_code' => 'GR', 'phone_code' => '+30'],
            ['name' => 'Turkey',               'country_code' => 'TR', 'phone_code' => '+90'],
            ['name' => 'Russia',               'country_code' => 'RU', 'phone_code' => '+7'],
            ['name' => 'Ukraine',              'country_code' => 'UA', 'phone_code' => '+380'],
            ['name' => 'Mexico',               'country_code' => 'MX', 'phone_code' => '+52'],
            ['name' => 'Brazil',               'country_code' => 'BR', 'phone_code' => '+55'],
            ['name' => 'Argentina',            'country_code' => 'AR', 'phone_code' => '+54'],
            ['name' => 'Chile',                'country_code' => 'CL', 'phone_code' => '+56'],
            ['name' => 'Colombia',             'country_code' => 'CO', 'phone_code' => '+57'],
            ['name' => 'Peru',                 'country_code' => 'PE', 'phone_code' => '+51'],
            ['name' => 'Venezuela',            'country_code' => 'VE', 'phone_code' => '+58'],
            ['name' => 'Uruguay',              'country_code' => 'UY', 'phone_code' => '+598'],
            ['name' => 'Paraguay',             'country_code' => 'PY', 'phone_code' => '+595'],
            ['name' => 'Bolivia',              'country_code' => 'BO', 'phone_code' => '+591'],
            ['name' => 'Ecuador',              'country_code' => 'EC', 'phone_code' => '+593'],
            ['name' => 'Japan',                'country_code' => 'JP', 'phone_code' => '+81'],
            ['name' => 'China',                'country_code' => 'CN', 'phone_code' => '+86'],
            ['name' => 'South Korea',          'country_code' => 'KR', 'phone_code' => '+82'],
            ['name' => 'India',                'country_code' => 'IN', 'phone_code' => '+91'],
            ['name' => 'Pakistan',             'country_code' => 'PK', 'phone_code' => '+92'],
            ['name' => 'Bangladesh',           'country_code' => 'BD', 'phone_code' => '+880'],
            ['name' => 'Sri Lanka',            'country_code' => 'LK', 'phone_code' => '+94'],
            ['name' => 'Nepal',                'country_code' => 'NP', 'phone_code' => '+977'],
            ['name' => 'Thailand',             'country_code' => 'TH', 'phone_code' => '+66'],
            ['name' => 'Vietnam',              'country_code' => 'VN', 'phone_code' => '+84'],
            ['name' => 'Indonesia',            'country_code' => 'ID', 'phone_code' => '+62'],
            ['name' => 'Malaysia',             'country_code' => 'MY', 'phone_code' => '+60'],
            ['name' => 'Singapore',            'country_code' => 'SG', 'phone_code' => '+65'],
            ['name' => 'Philippines',          'country_code' => 'PH', 'phone_code' => '+63'],
            ['name' => 'Hong Kong',            'country_code' => 'HK', 'phone_code' => '+852'],
            ['name' => 'Taiwan',               'country_code' => 'TW', 'phone_code' => '+886'],
            ['name' => 'New Zealand',          'country_code' => 'NZ', 'phone_code' => '+64'],
            ['name' => 'South Africa',         'country_code' => 'ZA', 'phone_code' => '+27'],
            ['name' => 'Egypt',                'country_code' => 'EG', 'phone_code' => '+20'],
            ['name' => 'Morocco',              'country_code' => 'MA', 'phone_code' => '+212'],
            ['name' => 'Nigeria',              'country_code' => 'NG', 'phone_code' => '+234'],
            ['name' => 'Kenya',                'country_code' => 'KE', 'phone_code' => '+254'],
            ['name' => 'Ghana',                'country_code' => 'GH', 'phone_code' => '+233'],
            ['name' => 'Ethiopia',             'country_code' => 'ET', 'phone_code' => '+251'],
            ['name' => 'Tanzania',             'country_code' => 'TZ', 'phone_code' => '+255'],
            ['name' => 'Algeria',              'country_code' => 'DZ', 'phone_code' => '+213'],
            ['name' => 'Tunisia',              'country_code' => 'TN', 'phone_code' => '+216'],
            ['name' => 'Saudi Arabia',         'country_code' => 'SA', 'phone_code' => '+966'],
            ['name' => 'United Arab Emirates', 'country_code' => 'AE', 'phone_code' => '+971'],
            ['name' => 'Qatar',                'country_code' => 'QA', 'phone_code' => '+974'],
            ['name' => 'Kuwait',               'country_code' => 'KW', 'phone_code' => '+965'],
            ['name' => 'Bahrain',              'country_code' => 'BH', 'phone_code' => '+973'],
            ['name' => 'Oman',                 'country_code' => 'OM', 'phone_code' => '+968'],
            ['name' => 'Israel',               'country_code' => 'IL', 'phone_code' => '+972'],
            ['name' => 'Jordan',               'country_code' => 'JO', 'phone_code' => '+962'],
            ['name' => 'Lebanon',              'country_code' => 'LB', 'phone_code' => '+961'],
            ['name' => 'Iraq',                 'country_code' => 'IQ', 'phone_code' => '+964'],
            ['name' => 'Iran',                 'country_code' => 'IR', 'phone_code' => '+98'],
            ['name' => 'Afghanistan',          'country_code' => 'AF', 'phone_code' => '+93'],
            ['name' => 'Kazakhstan',           'country_code' => 'KZ', 'phone_code' => '+7'],
            ['name' => 'Uzbekistan',           'country_code' => 'UZ', 'phone_code' => '+998'],
        ];
    }

    private function categoryNames(int $count): array
    {
        $base = [
            'Clear Aligners', 'Retainers', 'Brackets', 'Wires', 'Bands',
            'Buccal Tubes', 'Elastics', 'Mini-Implants (TADs)', 'Bonding Materials', 'Adhesives',
            'Etchants', 'Pliers', 'Cutters', 'Scalers', 'Mirrors',
            'Probes', 'Forceps', 'Burs', 'Diamond Discs', 'IPR Strips',
            'Articulation Paper', 'Wax', 'Impression Materials', 'Trays', 'Models',
            '3D Printing Resins', 'Scanner Accessories', 'Sterilization Pouches', 'Disinfectants', 'Gloves',
            'Masks', 'Eyewear', 'Patient Bibs', 'Suction Tips', 'Cotton Rolls',
            'Anesthetics', 'Topicals', 'Sealants', 'Fluoride', 'Whitening',
            'Mouthguards', 'Night Guards', 'Splints', 'Functional Appliances', 'Headgear',
            'Expanders', 'Lingual Appliances', 'Space Maintainers', 'Habit Appliances', 'Power Chains',
        ];
        for ($i = count($base); $i < $count; $i++) {
            $base[] = 'Category ' . ($i + 1);
        }
        return array_slice($base, 0, $count);
    }

    private function subcategoryName(int $i): string
    {
        $prefixes = ['Premium', 'Standard', 'Economy', 'Pro', 'Lite', 'Advanced', 'Pediatric', 'Adult', 'Express', 'Long-Term'];
        $suffixes = ['Series', 'Kit', 'Pack', 'Set', 'Line', 'Edition', 'Bundle'];
        return $prefixes[$i % count($prefixes)] . ' ' . $suffixes[($i / count($prefixes)) % count($suffixes)] . ' ' . ($i + 1);
    }

    private function productName(int $i): string
    {
        $brands = ['Invisalign', 'Spark', 'ClearCorrect', 'SureSmile', 'Orchestrate', '3M Clarity', 'Damon', 'Empower', 'OrthoClassic', 'Forestadent'];
        return $brands[$i % count($brands)] . ' Treatment ' . ($i + 1);
    }

    private function scannerNames(): array
    {
        return [
            'Aoralscan 3', 'Aoralscan Elite', 'iTero Element 5D', 'iTero Element Plus', 'iTero Lumina',
            'Trios 3', 'Trios 4', 'Trios 5', 'Medit i500', 'Medit i600', 'Medit i700', 'Medit i900',
            'Primescan', 'Primescan 2', 'Omnicam', 'CS 3600', 'CS 3700', 'CS 3800',
            'Virtuo Vivo', 'Emerald S', 'Emerald', 'DEXIS IS 3800W', 'DEXIS IS ScanFlow',
            'Runyes DScan', 'Launca DL-206', 'Launca DL-300', 'Panda Smart', 'Panda P3',
            'Helios 600', 'Helios 500',
        ];
    }

    private function practiceName(int $i): string
    {
        $prefixes = ['Smile', 'Bright', 'Premier', 'Family', 'Modern', 'Advanced', 'Pure', 'Gentle', 'Caring', 'Sunshine', 'Pearl', 'Capital', 'Riverside', 'Lakeside', 'Mountain'];
        $cores    = ['Dental', 'Orthodontics', 'Smile Studio', 'Family Dentistry', 'Orthodontic Care', 'Dental Group', 'Dental Associates', 'Ortho Center', 'Dental Arts'];
        $suffixes = ['LLC', 'PC', 'Inc', '& Associates', 'Clinic', 'Specialists', '', '', ''];

        $p = $prefixes[$i % count($prefixes)];
        $c = $cores[($i / count($prefixes)) % count($cores)];
        $s = $suffixes[($i / (count($prefixes) * count($cores))) % count($suffixes)];

        return trim("{$p} {$c} {$s}") . ' #' . ($i + 1);
    }
}
