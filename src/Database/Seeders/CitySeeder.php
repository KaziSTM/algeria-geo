<?php

namespace KaziSTM\AlgeriaGeo\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use KaziSTM\AlgeriaGeo\Models\City;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cities')->truncate();

        $jsonPath = __DIR__ . '/../../../data/wilayas.json';

        if (! File::exists($jsonPath)) {
            $this->command->error("Wilayas JSON file not found");
            return;
        }

        $cities = json_decode(File::get($jsonPath), true, 512, JSON_THROW_ON_ERROR);

        City::unsetEventDispatcher();

        collect($cities)
            ->map(fn($city) => [
                'id'           => $city['id'],
                'code'         => $city['code'],
                'name'         => $city['name'],
                'arabic_name'  => $city['arabic_name'],
                'latitude'     => isset($city['latitude']) ? (float) $city['latitude'] : null,
                'longitude'    => isset($city['longitude']) ? (float) $city['longitude'] : null,
            ])
            ->chunk(1000)
            ->each(fn($chunk) => City::insert($chunk->toArray()));

        $this->command->info('Cities seeded successfully.');
    }
}
