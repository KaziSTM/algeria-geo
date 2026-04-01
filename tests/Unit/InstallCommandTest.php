<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use KaziSTM\AlgeriaGeo\Models\City;

/*
|--------------------------------------------------------------------------
| Install Command Tests
|--------------------------------------------------------------------------
*/

it('skips when tables exist and no force', function () {
    // Tables exist because Testbench loads migrations
    $this->artisan('algeria-geo:install')
        ->expectsOutputToContain('already exist')
        ->assertExitCode(0);
});

it('runs migrations when tables do not exist', function () {
    Schema::dropAllTables();

    $this->artisan('algeria-geo:install')
        ->expectsOutputToContain('Running migrations')
        ->assertExitCode(0);

    expect(Schema::hasTable('cities'))->toBeTrue();
    expect(Schema::hasTable('communes'))->toBeTrue();
});

it('runs seeder when seed option is passed', function () {
    File::shouldReceive('exists')->andReturn(true);

    File::shouldReceive('get')
        ->andReturnUsing(function ($path) {
            if (str_contains($path, 'wilayas')) {
                return json_encode([
                    [
                        'id' => 1,
                        'code' => 1,
                        'name' => 'Adrar',
                        'arabic_name' => 'أدرار',
                        'slug' => 'adrar',
                    ],
                ]);
            }

            if (str_contains($path, 'communes')) {
                return json_encode([
                    [
                        'id' => 1,
                        'post_code' => '01000',
                        'name' => 'Adrar',
                        'arabic_name' => 'أدرار',
                        'slug' => 'adrar',
                        'wilaya_id' => 1,
                    ],
                ]);
            }

            return json_encode([]);
        });

    $this->artisan('algeria-geo:install --seed')
        ->expectsOutputToContain('Seeding database')
        ->assertExitCode(0);

    expect(City::count())->toBe(1);
});

it('force runs even if tables exist', function () {
    File::partialMock()
        ->shouldReceive('exists')->andReturn(true);

    File::partialMock()
        ->shouldReceive('get')->andReturn(json_encode([]));

    $this->artisan('algeria-geo:install --force')
        ->expectsOutputToContain('Using --force option')
        ->assertExitCode(0);
});

it('runs successfully when migrations are already applied', function () {
    // First run
    $this->artisan('algeria-geo:install --force')->run();

    // Second run
    $this->artisan('algeria-geo:install --force')
        ->assertExitCode(0);
});

it('skips seeding when not requested on fresh install', function () {
    Schema::dropAllTables();

    $this->artisan('algeria-geo:install')
        ->expectsOutputToContain('Skipping database seeding')
        ->assertExitCode(0);
});

it('forces seeding when using force option', function () {
    File::partialMock()
        ->shouldReceive('exists')->andReturn(true);

    File::partialMock()
        ->shouldReceive('get')->andReturn(json_encode([]));

    $this->artisan('algeria-geo:install --force')
        ->expectsOutputToContain('Forcing seeding')
        ->assertExitCode(0);
});