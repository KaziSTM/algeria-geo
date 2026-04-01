<?php

namespace KaziSTM\AlgeriaGeo\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use KaziSTM\AlgeriaGeo\Models\City;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = __DIR__ . '/../../../data/wilayas.json';

        if (! File::exists($jsonPath)) {
            return;
        }

        $cities = json_decode(File::get($jsonPath), true, 512, JSON_THROW_ON_ERROR);

        collect($cities)
            ->map(fn($city) => [
                'id'           => $city['id'],
                'code'         => $city['code'],
                'name'         => $city['name'],
                'arabic_name'  => $city['arabic_name'],
                'slug'         => $city['slug'],
                'latitude'     => $city['latitude'] ?? null,
                'longitude'    => $city['longitude'] ?? null,
            ])
            ->chunk(1000)
            ->each(
                fn($chunk) =>
                City::upsert(
                    $chunk->toArray(),
                    ['id'], // unique key
                    ['code', 'name', 'arabic_name','slug', 'latitude', 'longitude']
                )
            );
    }
}
