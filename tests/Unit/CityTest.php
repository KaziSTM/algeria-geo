<?php

use KaziSTM\AlgeriaGeo\Models\City;

it('can find a city by code', function () {
    $city = City::create([
        'id' => 31,
        'code' => 31,
        'name' => 'Oran',
        'arabic_name' => 'وهران',
        'slug' => 'oran'
    ]);

    expect(City::findByCode(31)?->id)->toBe(31);
});

it('can find a city by slug', function () {
    City::create([
        "id" => 16,
        "code" => 16,
        "name" => "Algiers",
        "arabic_name" => "الجزائر",
        "slug" => "algiers",
    ]);

    expect(City::findBySlug('algiers')?->name)->toBe('Algiers');
});

it('returns null when city code not found', function () {
    expect(City::findByCode(999))->toBeNull();
});

it('searches by name or arabic name', function () {
    City::create([
        "id" => 16,
        "code" => 16,
        "name" => "Algiers",
        "arabic_name" => "الجزائر",
        "slug" => "algiers",
    ]);

    // Match search to the data created
    expect(City::searchByName('Alg')->count())->toBe(1);
    expect(City::searchByName('الجز')->count())->toBe(1);
});

it('returns coordinates correctly', function () {
    $city = City::create([
        "id" => 31,
        "code" => 31,
        "name" => "Oran",
        "arabic_name" => "وهران",
        "slug" => "oran",
        "latitude" => 35.6987388,
        "longitude" => -0.6349319,
    ]);

    // Use floats to match your model's $casts
    expect($city->getCoordinates())->toBe([
        "latitude" => 35.6987388,
        "longitude" => -0.6349319,
    ]);
});

it('returns null coordinates when missing', function () {
    $city = City::create([
        'id' => 999, // Explicit ID to avoid conflicts with real wilaya data
        'code' => 99,
        'name' => 'Test Wilaya',
        'arabic_name' => 'ولاية تجريبية',
        'slug' => 'test-wilaya',
    ]);

    expect($city->getCoordinates())->toBeNull();
});

it('returns display name based on preference', function () {
    $city = City::create([
        'id' => 31,
        'code' => 31,
        'name' => 'Oran',
        'arabic_name' => 'وهران',
        'slug' => 'oran',
    ]);

    // Test default (English/Name)
    expect($city->getDisplayName())->toBe('Oran');

    // Test explicit Arabic preference
    expect($city->getDisplayName(preferArabic: true))->toBe('وهران');
});

it('returns original query if search term is empty', function () {
    $query = City::searchByName('   ');
    expect($query->toSql())->not->toContain('LIKE');
});

it('can get display name in arabic', function () {
    $city = new City(['name' => 'Adrar', 'arabic_name' => 'أدرار']);
    expect($city->getDisplayName(true))->toBe('أدرار');
});

it('filters by slug scope', function () {
    City::create([
        'id' => 1,
        'code' => 1,
        'name' => 'Adrar',
        'arabic_name' => 'أدرار',
        'slug' => 'adrar',
    ]);

    expect(City::slug('adrar')->count())->toBe(1);
});

it('filters by code scope', function () {
    City::create([
        'id' => 2,
        'code' => 2,
        'name' => 'Chlef',
        'arabic_name' => 'الشلف',
        'slug' => 'chlef',
    ]);

    expect(City::code(2)->count())->toBe(1);
});

it('has many communes', function () {
    $city = City::create([
        'id' => 31,
        'code' => 31,
        'name' => 'Oran',
        'arabic_name' => 'وهران',
        'slug' => 'oran',
    ]);

    \KaziSTM\AlgeriaGeo\Models\Commune::create([
        'id' => 1,
        'name' => 'Bir El Djir',
        'arabic_name' => 'بئر الجير',
        'slug' => 'bir-el-djir',
        'wilaya_id' => 31,
    ]);

    expect($city->communes)->toHaveCount(1);
});