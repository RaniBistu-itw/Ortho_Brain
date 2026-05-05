<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Practice;
use App\Models\Scanner;
use Illuminate\Database\Seeder;

/**
 * Demo data so the Add Case feature works out-of-the-box on a fresh DB:
 *  - 3 sample practices (no geo dependency)
 *  - 6 sample scanners across the common brands
 *  - links the seeded test doctor to the first practice
 *
 * Idempotent. Runs after DoctorSeeder.
 */
class CaseDemoSeeder extends Seeder
{
    public function run(): void
    {
        $practices = [
            ['name' => 'Demo Dental Practice',     'website' => 'https://demo-dental.example.com',    'phone_country_code' => '+1', 'phone_number' => '5550100001'],
            ['name' => 'Promo Indp Practice',      'website' => 'https://promoindp.example.com',      'phone_country_code' => '+1', 'phone_number' => '5550100002'],
            ['name' => 'Cedar Park Orthodontics',  'website' => 'https://cedarpark.example.com',      'phone_country_code' => '+1', 'phone_number' => '5550100003'],
        ];

        foreach ($practices as $p) {
            Practice::firstOrCreate(
                ['name' => $p['name']],
                array_merge($p, ['status' => 'ACTIVE'])
            );
        }

        // Link test doctor to the first practice if not already linked.
        $firstPractice = Practice::where('status', 'ACTIVE')->orderBy('id')->first();
        if ($firstPractice) {
            Doctor::query()
                ->whereNull('practice_id')
                ->update(['practice_id' => $firstPractice->id]);
        }

        $scanners = [
            'iTero — Element 5D',
            'iTero — Element 5D Plus',
            '3Shape — Trios 4',
            '3Shape — Trios 5',
            'Medit — i700',
            'Other / Non-digital — PVS',
        ];

        foreach ($scanners as $name) {
            Scanner::firstOrCreate(
                ['name' => $name],
                ['status' => 'ACTIVE', 'description' => 'Seeded for Add Case demo.']
            );
        }

        $this->command?->info('CaseDemoSeeder: ' . Practice::count() . ' practices, '
            . Scanner::count() . ' scanners, '
            . Doctor::whereNotNull('practice_id')->count() . ' linked doctors.');
    }
}
