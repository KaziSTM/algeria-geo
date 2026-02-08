<?php

namespace KaziSTM\AlgeriaGeo\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use KaziSTM\AlgeriaGeo\Models\Commune;

class CommuneSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = __DIR__ . '/../../../data/communes.json';

        if (! File::exists($jsonPath)) {
            return;
        }

        $communes = json_decode(File::get($jsonPath), true, 512, JSON_THROW_ON_ERROR);

        collect($communes)
            ->map(fn($commune) => [
                'id'          => $commune['id'],
                'post_code'   => $commune['post_code'],
                'name'        => $commune['name'],
                'wilaya_id'   => $commune['wilaya_id'],
                'arabic_name' => $commune['arabic_name'],
                'latitude'    => $commune['latitude'] ?? null,
                'longitude'   => $commune['longitude'] ?? null,
            ])
            ->chunk(1000)
            ->each(
                fn($chunk) =>
                Commune::upsert(
                    $chunk->toArray(),
                    ['id'],
                    ['post_code', 'name', 'wilaya_id', 'arabic_name', 'latitude', 'longitude']
                )
            );
    }
}
