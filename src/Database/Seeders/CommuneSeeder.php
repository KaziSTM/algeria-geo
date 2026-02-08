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
        DB::table('communes')->truncate();

        $jsonPath = __DIR__ . '/../../../data/communes.json';

        if (! File::exists($jsonPath)) {
            $this->command->error("Communes JSON file not found");
            return;
        }

        $communes = json_decode(File::get($jsonPath), true, 512, JSON_THROW_ON_ERROR);

        Commune::unsetEventDispatcher(); 
        collect($communes)
            ->map(fn($commune) => [
                'id'           => $commune['id'],
                'post_code'    => $commune['post_code'],
                'name'         => $commune['name'],
                'wilaya_id'    => $commune['wilaya_id'],
                'arabic_name'  => $commune['arabic_name'],
                'latitude'     => isset($commune['latitude']) ? (float) $commune['latitude'] : null,
                'longitude'    => isset($commune['longitude']) ? (float) $commune['longitude'] : null,
            ])
            ->chunk(1000)
            ->each(fn($chunk) => Commune::insert($chunk->toArray()));

        $this->command->info('Communes seeded successfully.');
    }
}
