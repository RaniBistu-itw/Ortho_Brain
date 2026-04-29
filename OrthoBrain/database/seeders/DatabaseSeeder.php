<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LocationMasterSeeder::class,
            ManageTypesSeeder::class,
            SuperAdminSeeder::class,
            MasterOptionsSeeder::class,
            PracticesSeeder::class,
            DoctorSeeder::class,
            CaseDemoSeeder::class,
            CaseDashboardSeeder::class,
            // Patient persistence layer (PR #69) + image persistence (PR #64)
            // need their own demo data so a fresh DB has populated wizards.
            PatientsDemoSeeder::class,
            CaseMediaDemoSeeder::class,
            // PerformanceTestSeeder::class, // run on-demand: php artisan db:seed --class=PerformanceTestSeeder
        ]);
    }
}
