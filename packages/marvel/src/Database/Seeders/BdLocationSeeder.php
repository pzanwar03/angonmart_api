<?php

namespace Marvel\Database\Seeders;

use Illuminate\Database\Seeder;
use Marvel\Database\Models\BdDistrict;
use Marvel\Database\Models\BdDivision;
use Marvel\Database\Models\BdThana;

class BdLocationSeeder extends Seeder
{
    /**
     * Seed Bangladesh Division → District → Thana data.
     * Re-running is safe: rows are upserted by id.
     *
     * php artisan db:seed --class=Marvel\\Database\\Seeders\\BdLocationSeeder
     */
    public function run(): void
    {
        $path = dirname(__DIR__, 3) . '/database/data/bd-locations.json';
        if (!file_exists($path)) {
            $this->command?->error("Bangladesh location data not found at {$path}");
            return;
        }

        $data = json_decode(file_get_contents($path), true);
        if (!is_array($data)) {
            $this->command?->error('Invalid Bangladesh location JSON.');
            return;
        }

        foreach ($data['divisions'] ?? [] as $division) {
            BdDivision::updateOrCreate(
                ['id' => $division['id']],
                [
                    'name' => $division['name'],
                    'bn_name' => $division['bn_name'] ?? null,
                ]
            );
        }

        foreach ($data['districts'] ?? [] as $district) {
            BdDistrict::updateOrCreate(
                ['id' => $district['id']],
                [
                    'division_id' => $district['division_id'],
                    'name' => $district['name'],
                    'bn_name' => $district['bn_name'] ?? null,
                ]
            );
        }

        foreach ($data['thanas'] ?? [] as $thana) {
            BdThana::updateOrCreate(
                ['id' => $thana['id']],
                [
                    'district_id' => $thana['district_id'],
                    'name' => $thana['name'],
                    'bn_name' => $thana['bn_name'] ?? null,
                ]
            );
        }

        $this->command?->info(sprintf(
            'Seeded %d divisions, %d districts, %d thanas.',
            count($data['divisions'] ?? []),
            count($data['districts'] ?? []),
            count($data['thanas'] ?? [])
        ));
    }
}
