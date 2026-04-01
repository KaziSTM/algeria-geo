<?php

use Illuminate\Support\Facades\File;
use KaziSTM\AlgeriaGeo\Database\Seeders\CommuneSeeder;
use KaziSTM\AlgeriaGeo\Models\Commune;

it('does nothing if json file is missing', function () {
    File::partialMock()->shouldReceive('exists')->andReturn(false);

    (new CommuneSeeder())->run();

    expect(Commune::count())->toBe(0);
});




it('does nothing if communes json file is missing', function () {
    // Use a regex pattern to match the end of the path
    File::shouldReceive('exists')
        ->with(Mockery::pattern('/communes\.json$/'))
        ->andReturn(false);

    $seeder = new \KaziSTM\AlgeriaGeo\Database\Seeders\CommuneSeeder();
    $seeder->run();

    expect(\KaziSTM\AlgeriaGeo\Models\Commune::count())->toBe(0);
});
it('ignores invalid commune rows', function () {
    \Illuminate\Support\Facades\File::shouldReceive('exists')->andReturn(true);
    \Illuminate\Support\Facades\File::shouldReceive('get')->andReturn(json_encode([
        ['invalid' => true],
    ]));

    (new \KaziSTM\AlgeriaGeo\Database\Seeders\CommuneSeeder())->run();

    expect(\KaziSTM\AlgeriaGeo\Models\Commune::count())->toBe(0);
});