<?php

namespace Database\Seeders;

use App\Models\BuccalCorridorOption;
use App\Models\Modality;
use App\Models\Specialty;
use App\Models\TreatmentModality;
use Illuminate\Database\Seeder;

class MasterOptionsSeeder extends Seeder
{
    public function run(): void
    {
        $modalities = [
            'Clear Aligner Therapy',
            'Braces',
            'Early Intervention',
        ];

        $specialties = [
            'General Dentist',
            'Orthodontist',
            'Pediatric Dentist',
            'Endodontist',
            'Oral & Maxillofacial Surgeon',
            'Periodontist',
            'Prosthodontist',
        ];

        $treatmentModalities = [
            'Clear Aligner Therapy',
            'Braces',
            'Orthopedics/Arch Development',
        ];

        $buccalCorridors = [
            'Defer to orthobrain®',
            'Expand to fill buccal corridors',
            'Do not expand molars',
            'Do not expand premolars or canines',
            'Maintain initial arch width',
        ];

        foreach ($modalities as $name) {
            Modality::firstOrCreate(['name' => $name]);
        }
        foreach ($specialties as $name) {
            Specialty::firstOrCreate(['name' => $name]);
        }
        foreach ($treatmentModalities as $name) {
            TreatmentModality::firstOrCreate(['name' => $name]);
        }
        foreach ($buccalCorridors as $name) {
            BuccalCorridorOption::firstOrCreate(['name' => $name]);
        }
    }
}
