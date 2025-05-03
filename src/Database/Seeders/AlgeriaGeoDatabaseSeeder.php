<?php

namespace KaziSTM\AlgeriaGeo\Database\Seeders;

use Illuminate\Database\Seeder;

class AlgeriaGeoDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            CitySeeder::class,
            CommuneSeeder::class,
        ]);
    }
}