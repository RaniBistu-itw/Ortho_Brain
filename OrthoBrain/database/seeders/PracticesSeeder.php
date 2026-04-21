<?php

namespace Database\Seeders;

use App\Models\Practice;
use App\Models\Zipcode;
use Illuminate\Database\Seeder;

class PracticesSeeder extends Seeder
{
    public function run(): void
    {
        // Pull all active zipcodes; cycle through them so practices spread across cities/states.
        $zips = Zipcode::with('city.state.country')
            ->where('status', 'ACTIVE')
            ->orderBy('id')
            ->get();

        if ($zips->isEmpty()) {
            $this->command?->warn('PracticesSeeder skipped: no ACTIVE zipcodes found. Run geo sample data first.');
            return;
        }

        $samples = [
            ['name' => 'Smile Dental Group',         'website' => 'https://smiledental.example.com',      'phone' => '5551110001'],
            ['name' => 'Bright Orthodontics',        'website' => 'https://brightortho.example.com',      'phone' => '5551110002'],
            ['name' => 'Perfect Smile Practice',     'website' => 'https://perfectsmile.example.com',     'phone' => '5551110003'],
            ['name' => 'Family Dental Care',         'website' => 'https://familydental.example.com',     'phone' => '5551110004'],
            ['name' => 'Advanced Orthodontic Lab',   'website' => 'https://advancedortho.example.com',    'phone' => '5551110005'],
            ['name' => 'Downtown Dental',            'website' => 'https://downtowndental.example.com',   'phone' => '5551110006'],
            ['name' => 'Sunrise Smile Clinic',       'website' => 'https://sunrisesmile.example.com',     'phone' => '5551110007'],
            ['name' => 'Metro Ortho Center',         'website' => 'https://metroortho.example.com',       'phone' => '5551110008'],
            ['name' => 'Cornerstone Dental',         'website' => 'https://cornerstonedental.example.com','phone' => '5551110009'],
            ['name' => 'Riverview Orthodontics',     'website' => 'https://riverview.example.com',        'phone' => '5551110010'],
            ['name' => 'Gentle Dental Studio',       'website' => 'https://gentledental.example.com',     'phone' => '5551110011'],
            ['name' => 'Pearl Smile Specialists',    'website' => 'https://pearlsmile.example.com',       'phone' => '5551110012'],
            ['name' => 'Oak Tree Dental',            'website' => 'https://oaktreedental.example.com',    'phone' => '5551110013'],
            ['name' => 'Alpine Ortho',               'website' => 'https://alpineortho.example.com',      'phone' => '5551110014'],
            ['name' => 'Lakeside Family Dentistry',  'website' => 'https://lakesidefamily.example.com',   'phone' => '5551110015'],
            ['name' => 'Parkview Smile Center',      'website' => 'https://parkviewsmile.example.com',    'phone' => '5551110016'],
            ['name' => 'Harbor Dental Associates',   'website' => 'https://harbordental.example.com',     'phone' => '5551110017'],
            ['name' => 'Midtown Orthodontics',       'website' => 'https://midtownortho.example.com',     'phone' => '5551110018'],
            ['name' => 'Starlight Dental',           'website' => 'https://starlightdental.example.com',  'phone' => '5551110019'],
            ['name' => 'Heritage Smile Clinic',      'website' => 'https://heritagesmile.example.com',    'phone' => '5551110020'],
            ['name' => 'Summit Orthodontics',        'website' => 'https://summitortho.example.com',      'phone' => '5551110021'],
            ['name' => 'Crescent Dental Care',       'website' => 'https://crescentdental.example.com',   'phone' => '5551110022'],
            ['name' => 'Elite Smile Design',         'website' => 'https://elitesmile.example.com',       'phone' => '5551110023'],
            ['name' => 'Willow Creek Orthodontics',  'website' => 'https://willowcreek.example.com',      'phone' => '5551110024'],
            ['name' => 'Skyline Dental Group',       'website' => 'https://skylinedental.example.com',    'phone' => '5551110025'],
            ['name' => 'Cedar Park Orthodontics',    'website' => 'https://cedarpark.example.com',        'phone' => '5551110026'],
            ['name' => 'Brookside Smile Studio',     'website' => 'https://brooksidesmile.example.com',   'phone' => '5551110027'],
            ['name' => 'Hilltop Dental Specialists', 'website' => 'https://hilltopdental.example.com',    'phone' => '5551110028'],
            ['name' => 'Coastal Orthodontic Care',   'website' => 'https://coastalortho.example.com',     'phone' => '5551110029'],
            ['name' => 'Main Street Dental',         'website' => 'https://mainstreetdental.example.com', 'phone' => '5551110030'],
        ];

        foreach ($samples as $i => $s) {
            $zip = $zips[$i % $zips->count()];

            Practice::firstOrCreate(
                ['name' => $s['name']],
                [
                    'owner_id'           => null,
                    'website'            => $s['website'],
                    'phone_country_code' => '+1_US',
                    'phone_number'       => $s['phone'],
                    'street_address_1'   => ($i + 1) . ' Main Street',
                    'street_address_2'   => null,
                    'zip_id'             => $zip->id,
                    'city_id'            => $zip->city?->id,
                    'state_id'           => $zip->city?->state?->id,
                    'country_id'         => $zip->city?->state?->country?->id,
                    'status'             => 'ACTIVE',
                ]
            );
        }
    }
}
