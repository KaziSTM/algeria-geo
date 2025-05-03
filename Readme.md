# Algeria Geo Data for Laravel by KaziSTM

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kazistm/algeria-geo.svg?style=flat-square)](https://packagist.org/packages/kazistm/algeria-geo)
[![Total Downloads](https://img.shields.io/packagist/dt/kazistm/algeria-geo.svg?style=flat-square)](https://packagist.org/packages/kazistm/algeria-geo)
[![License](https://img.shields.io/packagist/l/kazistm/algeria-geo.svg?style=flat-square)](https://packagist.org/packages/kazistm/algeria-geo)
This Laravel package provides a straightforward way to integrate Algerian administrative divisions (Wilayas/Cities and
Communes) into your application. It comes complete with Eloquent models, database migrations, seeders populated from
official data, and helpful query scopes and methods, including geospatial radius searching.

---

## Table of Contents

* [Features](#features)
* [Installation](#installation)
    * [1. Require Package](#1-require-package)
    * [2. Run Install Command](#2-run-install-command)
    * [3. Optional Publishing](#3-optional-publishing)
* [Usage](#usage)
    * [Models](#models)
        * [City (Wilaya)](#city-wilaya)
        * [Commune](#commune)
    * [Basic Retrieval](#basic-retrieval)
    * [Relationships](#relationships)
        * [City to Communes](#city-to-communes)
        * [Commune to City](#commune-to-city)
    * [Static Finders](#static-finders)
        * [City::findByCode()](#cityfindbycode)
        * [Commune::findByPostCode()](#communefindbypostcode)
    * [Query Scopes](#query-scopes)
        * [searchByName()](#searchbyname)
        * [code() (City)](#code-city)
        * [postCode() (Commune)](#postcode-commune)
        * [withinRadius() (Commune)](#withinradius-commune)
    * [Helper Methods](#helper-methods)
        * [getCoordinates()](#getcoordinates)
        * [getDisplayName()](#getdisplayname)
* [Data Source](#data-source)
* [Contributing](#contributing)
* [License](#license)

---

## Features

* **Eloquent Models:** `City` (Wilaya) and `Commune` models ready to use.
* **Relationships:** Pre-defined `hasMany` (City->Communes) and `belongsTo` (Commune->City) relationships.
* **Database Migrations:** Sets up `cities` and `communes` tables with appropriate types and indices.
* **Database Seeders:** Populates tables with comprehensive data for 58 Wilayas and their Communes.
* **Easy Installation:** Single Artisan command (`algeria-geo:install`) for migration and optional seeding.
* **Helpful Query Scopes:** Includes scopes for searching by name, code, postal code, and **geospatial radius**.
* **Convenience Methods:** Helpers for retrieving coordinates and display names.
* **Laravel Compatibility:** Supports Laravel 10, 11+.
* **Well-Documented:** Models include detailed PHPDocs.

## Installation

### 1. Require Package

Install the package via Composer:

```bash
composer require kazistm/algeria-geo
```

## 2. Run Install Command

This command checks if the tables exist, runs the migrations if needed, and optionally seeds the database.

### Bash

```bash
# Run migrations only (if tables don't exist)
php artisan algeria-geo:install

# Run migrations AND seed the database (recommended for first install)
php artisan algeria-geo:install --seed

# Force migrations/seeding even if tables exist (use with caution!)
php artisan algeria-geo:install --seed --force
```

The command will inform you if the tables already exist and skip the process unless --force is used.

## 3. Optional Publishing

You typically don't need to publish assets, as the package runs migrations directly from its vendor directory.  
However, if you need deep customization:

### Bash

```bash
# Publish Migrations (if you want to modify them BEFORE migrating)
php artisan vendor:publish --tag=algeria-geo-migrations

# Publish Seeders (if you want to modify the seeding logic)
php artisan vendor:publish --tag=algeria-geo-seeders

```

## Usage

### Models

Access the models using their namespaces:

```php
use KaziSTM\AlgeriaGeo\Models\City;
use KaziSTM\AlgeriaGeo\Models\Commune;
```

### City (Wilaya)

Represents an Algerian Wilaya.

| Column        | Type        | Description                               |
|---------------|-------------|-------------------------------------------|
| `id`          | Primary Key | Unique identifier                         |
| `code`        | `int`       | Wilaya code (e.g., `16`)                  |
| `name`        | `string`    | Wilaya name (e.g., `"Alger"`)             |
| `arabic_name` | `string`    | Wilaya name in Arabic (e.g., `"الجزائر"`) |
| `longitude`   | `float      | null`                                     | Optional longitude                     |
| `latitude`    | `float      | null`                                     | Optional latitude                      |
| `created_at`  | `timestamp` | Timestamp                                 |
| `updated_at`  | `timestamp` | Timestamp                                 |

---

### Commune (Daira)

Represents an Algerian Commune (Daira).

| Column        | Type        | Description                |
|---------------|-------------|----------------------------|
| `id`          | Primary Key | Unique identifier          |
| `post_code`   | `string     | null`                      | Postal code (nullable)                                       |
| `name`        | `string`    | Commune name               |
| `wilaya_id`   | `int`       | Foreign key to `cities.id` |
| `arabic_name` | `string`    | Commune name in Arabic     |
| `longitude`   | `float      | null`                      | Optional longitude                                            |
| `latitude`    | `float      | null`                      | Optional latitude                                             |
| `distance`    | `float      | null`                      | Available after using `withinRadius` scope                   |
| `created_at`  | `timestamp` | Timestamp                  |
| `updated_at`  | `timestamp` | Timestamp                  |

### Basic Retrieval

```php
// Get all Cities (Wilayas), ordered by code
$cities = City::orderBy('code')->get();

// Get all Communes, ordered by name
$communes = Commune::orderBy('name')->get();

// Find a specific City by ID
$city = City::find(16); // Alger

// Find a specific Commune by ID
$commune = Commune::find(554); // Alger Centre
```

### Relationships

#### City to Communes

```php
$algiersCity = City::find(16);

if ($algiersCity) {
    // Access the collection of Communes belonging to the City
    $algiersCommunes = $algiersCity->communes; // Returns Eloquent Collection

    // You can also query the relationship
    $firstCommune = $algiersCity->communes()->orderBy('name')->first();
}
```

#### Commune to City

```php
$koubaCommune = Commune::find(571); // Kouba

if ($koubaCommune) {
    // Access the City model the Commune belongs to
    $city = $koubaCommune->city; // Returns City model (Alger)

    echo "Commune: " . $koubaCommune->name . " belongs to City: " . $city->name;
    // Output: Commune: Kouba belongs to City: Alger
}
```

### Static Finders

Convenient methods to find a single record by a specific attribute.

#### `City::findByCode()`

Finds a City (Wilaya) by its official code.

```php
$oranCity = City::findByCode(31); // Returns the City model for Oran, or null

if ($oranCity) {
    echo "Found: " . $oranCity->name;
} else {
    echo "City with code 31 not found.";
}
```

#### `Commune::findByPostCode()`

Finds the first Commune matching the given postal code.  
Note: Postal codes might not be strictly unique across all communes in some datasets.

```php
$commune = Commune::findByPostCode('31001'); // e.g., Oran

if ($commune) {
    echo "Found Commune: " . $commune->name . " in City: " . $commune->city->name;
} else {
    echo "Commune with post code 31001 not found.";
}
```

#### `searchByName()`

Available on both City and Commune. Searches the `name` and `arabic_name` columns.

```php
// Search Cities
$cities = City::searchByName('Bordj')->orderBy('name')->get();

// Search Communes
$communes = Commune::searchByName('وادي')->orderBy('name')->get();

// Chain with other constraints
$algiersCommunesStartingWithS = Commune::where('wilaya_id', 16)
                                    ->searchByName('S')
                                    ->orderBy('name')
                                    ->get();
```

#### `code()` (City)

Filters Cities by their official code.

```php
$city = City::code(5)->first(); // Batna
```

#### `postCode()` (Commune)

Filters Communes by their postal code.

```php
$communes = Commune::postCode('05001')->get(); // Communes with this post code
```

#### `withinRadius()` (Commune)

Finds Communes within a specified radius of a given latitude/longitude point using the Haversine formula.

**Parameters:**

- `$latitude` (float): Latitude of the center point.
- `$longitude` (float): Longitude of the center point.
- `$radius` (int, optional): The radius distance. Defaults to 10.
- `$unit` (string, optional): The unit for the radius ('km' or 'miles'). Defaults to 'km'.

**Returns:**

- An Eloquent Builder instance. When results are retrieved (`get()`, `first()`, etc.), each Commune model will have an
  additional `distance` attribute containing the calculated distance from the center point in the specified unit.

**Example (Kilometers):**

```php
// Find communes within 15km of central Algiers
$centerLat = 36.7753;
$centerLon = 3.0588;
$radiusKm = 15;

$nearbyCommunes = Commune::withinRadius($centerLat, $centerLon, $radiusKm, 'km')->get();

echo "Communes within {$radiusKm}km of {$centerLat},{$centerLon}:\n";
foreach ($nearbyCommunes as $comm) {
    // Access the calculated distance
    $distance = round($comm->distance, 2);
    echo "- {$comm->getDisplayName()} ({$distance} km)\n";
}
```

#### `withinRadius()` (Commune)

Finds Communes within a specified radius of a given latitude/longitude point using the Haversine formula.

**Parameters:**

- `$latitude` (float): Latitude of the center point.
- `$longitude` (float): Longitude of the center point.
- `$radius` (int, optional): The radius distance. Defaults to 10.
- `$unit` (string, optional): The unit for the radius ('km' or 'miles'). Defaults to 'km'.

**Returns:**

- An Eloquent Builder instance. When results are retrieved (`get()`, `first()`, etc.), each Commune model will have an
  additional `distance` attribute containing the calculated distance from the center point in the specified unit.

**Example (Kilometers):**

```php
// Find communes within 15km of central Algiers
$centerLat = 36.7753;
$centerLon = 3.0588;
$radiusKm = 15;

$nearbyCommunes = Commune::withinRadius($centerLat, $centerLon, $radiusKm, 'km')->get();

echo "Communes within {$radiusKm}km of {$centerLat},{$centerLon}:\n";
foreach ($nearbyCommunes as $comm) {
    // Access the calculated distance
    $distance = round($comm->distance, 2);
    echo "- {$comm->getDisplayName()} ({$distance} km)\n";
}
```

Note: This query relies on the **latitude** and **longitude** columns being populated in your communes table. Accuracy
depends
on the quality of the coordinate data. Communes with `NULL` coordinates are automatically excluded from the radius
search.

---

## Helper Methods

Instance methods available on retrieved models.

### `getCoordinates()`

Available on both **City** and **Commune**. Returns coordinates as an associative array or `null`.

**PHP Example:**

```php
$oranCity = City::findByCode(31);
$coords = $oranCity?->getCoordinates();
// $coords will be ['latitude' => 35.6987..., 'longitude' => -0.6349...] or null

$oranCommune = Commune::findByPostCode('31001');
$communeCoords = $oranCommune?->getCoordinates();
// $communeCoords will be ['latitude' => 35.6987..., 'longitude' => -0.6349...] or null
```

### `getDisplayName()`

Available on both **City** and **Commune**. Returns the name, optionally preferring the Arabic name.

**PHP Example:**

```php
$city = City::find(16); // Alger
echo $city->getDisplayName();          // Output: Alger
echo $city->getDisplayName(true);      // Output: الجزائر

$commune = Commune::find(554); // Alger Centre
echo $commune->getDisplayName();       // Output: Alger Centre
echo $commune->getDisplayName(true);   // Output: الجزائر الوسطى
```

---

### Data Source

The data used in the seeders originates from publicly available datasets for Algerian Wilayas and Communes, typically
including names, codes, and geographical coordinates. Data accuracy reflects the source data available at the time of
package creation/update.

---

### Contributing

Contributions (bug reports, feature requests, pull requests) are welcome. Please refer to the GitHub repository issues
and pull request sections.

---

### License

This package is open-source software licensed under the MIT license.

---
