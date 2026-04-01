<?php

use KaziSTM\AlgeriaGeo\Models\City;
use KaziSTM\AlgeriaGeo\Models\Commune;

beforeEach(function () {
    // Create a parent Wilaya to satisfy foreign key constraints
    City::create([
        'id' => 48,
        'code' => 48,
        'name' => 'Relizane',
        'arabic_name' => 'غليزان',
        'slug' => 'relizane'
    ]);
});

it('can find a commune by slug', function () {
    // Data based on your updated requirements
    Commune::create([
        'id' => 1541,
        'post_code' => '48038',
        'name' => 'Dar Ben Abdellah',
        'arabic_name' => 'دار بن عبد الله',
        'slug' => 'dar-ben-abdellah',
        'wilaya_id' => 48,
        'latitude' => 35.7015695,
        'longitude' => 0.6865200,
    ]);

    $commune = Commune::findBySlug('dar-ben-abdellah');

    expect($commune)->not->toBeNull()
        ->and($commune->name)->toBe('Dar Ben Abdellah')
        ->and($commune->id)->toBe(1541);
});

it('returns null when commune slug does not exist', function () {
    expect(Commune::findBySlug('non-existent-slug'))->toBeNull();
});

it('can find commune by post code', function () {
    Commune::create([
        'id' => 1540,
        'post_code' => '48037',
        'name' => 'Hamri',
        'arabic_name' => 'الحمري',
        'slug' => 'hamri',
        'wilaya_id' => 48,
    ]);

    expect(Commune::findByPostCode('48037'))->not->toBeNull();
});

it('searches communes by name', function () {
    Commune::create([
        'id' => 100,
        'post_code' => '31000',
        'name' => 'Oran',
        'arabic_name' => 'وهران',
        'slug' => 'oran',
        'wilaya_id' => 48,
    ]);

    expect(Commune::searchByName('Ora')->count())->toBe(1);
    expect(Commune::searchByName('وهران')->count())->toBe(1);
});

it('returns coordinates as floats', function () {
    $commune = Commune::create([
        'id' => 200,
        'post_code' => '00000',
        'name' => 'Geo Test',
        'arabic_name' => 'تجربة',
        'slug' => 'geo-test',
        'wilaya_id' => 48,
        'latitude' => 35.1234,
        'longitude' => 0.5678,
    ]);

    expect($commune->getCoordinates())->toBe([
        'latitude' => 35.1234,
        'longitude' => 0.5678,
    ]);
});

it('belongs to a city', function () {
    $city = City::create(['id' => 31, 'code' => 31, 'name' => 'Oran', 'arabic_name' => 'وهران', 'slug' => 'oran']);
    $commune = Commune::create([
        'id' => 1,
        'name' => 'Bir El Djir',
        'wilaya_id' => 31,
        'slug' => 'bir-el-djir',
        'arabic_name' => 'بئر الجير'
    ]);

    expect($commune->city)->toBeInstanceOf(City::class);
    expect($commune->city->id)->toBe(31);
});

it('can find communes within a radius', function () {
    // Oran center
    $lat = 35.6911;
    $lng = -0.6417;

    Commune::create([
        'id' => 1, 'name' => 'Near', 'slug' => 'near', 'wilaya_id' => 31, 'arabic_name' => 'قريب',
        'latitude' => 35.6915, 'longitude' => -0.6410
    ]);

    Commune::create([
        'id' => 2, 'name' => 'Far', 'slug' => 'far', 'wilaya_id' => 31, 'arabic_name' => 'بعيد',
        'latitude' => 34.0000, 'longitude' => 2.0000
    ]);

    $results = Commune::withinRadius($lat, $lng, 10)->get();

    expect($results)->toHaveCount(1);
    expect($results->first()->name)->toBe('Near');
    expect($results->first()->distance)->toBeLessThan(10);
});

it('returns empty search if term is empty', function () {
    expect(Commune::searchByName('')->toSql())->not->toContain('LIKE');
});

it('can get display name in arabic', function () {
    $commune = new Commune(['name' => 'Oran', 'arabic_name' => 'وهران']);
    expect($commune->getDisplayName(true))->toBe('وهران');
    expect($commune->getDisplayName(false))->toBe('Oran');
});

