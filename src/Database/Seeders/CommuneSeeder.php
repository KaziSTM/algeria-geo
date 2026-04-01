<?php

namespace KaziSTM\AlgeriaGeo\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
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
            // ✅ Ensure minimum required fields exist
            ->filter(fn ($commune) =>
            isset(
                $commune['id'],
                $commune['name'],
                $commune['wilaya_id']
            )
            )

            // ✅ Normalize data safely
            ->map(function ($commune) {
                $slug = $commune['slug']
                    ?? Str::slug($commune['name'] . '-' . $commune['wilaya_id'] . '-' . $commune['id']);

                return [
                    'id'          => $commune['id'],
                    'post_code'   => $commune['post_code'] ?? null,
                    'name'        => $commune['name'],
                    'wilaya_id'   => $commune['wilaya_id'],
                    'arabic_name' => $commune['arabic_name'] ?? $commune['name'],
                    'slug'        => $slug,
                    'latitude'    => $commune['latitude'] ?? null,
                    'longitude'   => $commune['longitude'] ?? null,
                ];
            })

            // ✅ CRITICAL: prevent unique(wilaya_id, slug) violations
            ->unique(fn ($commune) => $commune['wilaya_id'] . '-' . $commune['slug'])
            ->values()

            // ✅ Chunk for performance + SQLite safety
            ->chunk(500)

            ->each(function ($chunk) {
                Commune::upsert(
                    $chunk->toArray(),
                    ['id'], // match on primary key
                    [
                        'post_code',
                        'name',
                        'wilaya_id',
                        'arabic_name',
                        'slug',
                        'latitude',
                        'longitude',
                        'updated_at',
                    ]
                );
            });
    }
}