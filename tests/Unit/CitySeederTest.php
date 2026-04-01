<?php

use Illuminate\Support\Facades\File;
use KaziSTM\AlgeriaGeo\Database\Seeders\CitySeeder;
use KaziSTM\AlgeriaGeo\Models\City;

it('does nothing if json file is missing', function () {
    File::partialMock()->shouldReceive('exists')->andReturn(false);

    (new CitySeeder())->run();

    expect(City::count())->toBe(0);
});

it('seeds cities from json', function () {
    File::partialMock()->shouldReceive('exists')->andReturn(true);
    File::partialMock()->shouldReceive('get')->andReturn(json_encode([
        [
            "id"=> 31,
            "code"=> 31,
            "name"=> "Oran",
            "arabic_name"=> "وهران",
            "slug"=> "oran",
            "latitude"=> 35.6987388,
            "longitude"=> -0.6349319,
        ]
    ]));

    (new CitySeeder())->run();

    expect(City::count())->toBe(1);
});