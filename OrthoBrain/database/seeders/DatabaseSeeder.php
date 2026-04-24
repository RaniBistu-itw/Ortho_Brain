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
        ]);
    }
}
