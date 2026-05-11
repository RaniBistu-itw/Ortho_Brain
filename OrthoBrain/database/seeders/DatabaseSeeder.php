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
            // PatientsDemoSeeder must run before CaseDashboardSeeder so each
            // seeded case can be linked to a real patient (drives the
            // "Patient Name" column on admin + doctor case lists).
            PatientsDemoSeeder::class,
            CaseDashboardSeeder::class,
            // Fills scanner/impressions/shipping/additional-info/initials/rejection
            // data for cases created above. Must run after CaseDashboardSeeder.
            CaseSectionDataSeeder::class,
            CaseMediaDemoSeeder::class,
            // PerformanceTestSeeder::class, // run on-demand: php artisan db:seed --class=PerformanceTestSeeder
        ]);
    }
}
