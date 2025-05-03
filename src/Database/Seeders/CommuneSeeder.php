<?php

namespace KaziSTM\AlgeriaGeo\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use KaziSTM\AlgeriaGeo\Models\Commune;

class CommuneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear the table first
        DB::table('communes')->delete();

        $jsonPath = __DIR__ . '/../../../data/communes.json'; // Path to your JSON file

        if (!File::exists($jsonPath)) {
            $this->command->error("Communes JSON file not found at {$jsonPath}");
            return;
        }

        $json = File::get($jsonPath);
        $communes = json_decode($json, true); //

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error("Error decoding Communes JSON: " . json_last_error_msg());
            return;
        }

        foreach ($communes as $communeData) { //
            Commune::create([ // Use the model to create records
                'id' => $communeData['id'], // Use existing ID from JSON
                'post_code' => $communeData['post_code'], //
                'name' => $communeData['name'], //
                'wilaya_id' => $communeData['wilaya_id'], //
                'arabic_name' => $communeData['arabic_name'], //
                // Ensure keys exist and handle potential type issues
                'latitude' => isset($communeData['latitude']) ? (float)$communeData['latitude'] : null, //
                'longitude' => isset($communeData['longitude']) ? (float)$communeData['longitude'] : null, //
            ]);
        }
        $this->command->info('Communes table seeded successfully!');
    }
}