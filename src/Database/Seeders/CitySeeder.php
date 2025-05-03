<?php

namespace KaziSTM\AlgeriaGeo\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use KaziSTM\AlgeriaGeo\Models\City;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear the table first
        DB::table('cities')->delete();

        $jsonPath = __DIR__ . '/../../../data/wilayas.json'; // Path to your JSON file

        if (!File::exists($jsonPath)) {
            $this->command->error("Wilayas JSON file not found at {$jsonPath}");
            return;
        }

        $json = File::get($jsonPath);
        $cities = json_decode($json, true); //

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error("Error decoding Wilayas JSON: " . json_last_error_msg());
            return;
        }

        foreach ($cities as $cityData) { //
            City::create([ // Use the model to create records
                'id' => $cityData['id'], // Use existing ID from JSON
                'code' => $cityData['code'], //
                'name' => $cityData['name'], //
                'arabic_name' => $cityData['arabic_name'], //
                // Ensure keys exist and handle potential type issues
                'longitude' => isset($cityData['longitude']) ? (float)$cityData['longitude'] : null, //
                'latitude' => isset($cityData['latitude']) ? (float)$cityData['latitude'] : null, //
            ]);
        }

        $this->command->info('Cities table seeded successfully!');
    }
}