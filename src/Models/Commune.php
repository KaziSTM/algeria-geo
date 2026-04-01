<?php

namespace KaziSTM\AlgeriaGeo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Represents an Algerian Commune.
 *
 * @property int $id
 * @property string|null $post_code The postal code of the commune.
 * @property string $name The name of the commune.
 * @property int $wilaya_id Foreign key referencing the City (Wilaya).
 * @property string $arabic_name The Arabic name of the commune.
 * @property string $slug The slug of the commune.
 * @property float|null $latitude
 * @property float|null $longitude
 * @property float|null $distance Calculated distance in radius searches.
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read City $city
 *
 * @method static Builder|Commune searchByName(string $term) Scope to search communes by name (English or Arabic).
 * @method static Builder|Commune postCode(string $postCode) Scope to filter communes by postal code.
 * @method static Builder|Commune withinRadius(float $latitude, float $longitude, int $radius = 10, string $unit = 'km') Scope to find communes within a given radius.
 */
class Commune extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'post_code',
        'name',
        'wilaya_id',
        'arabic_name',
        'slug',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'wilaya_id' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'distance' => 'float',
    ];

    /**
     * Find a commune by its postal code.
     * Returns null if not found. Note: Post codes might not be unique across all communes.
     * Consider using findByPostCodeAndCity() if needed.
     *
     * @param  string  $postCode  The Postal code.
     * @return static|null
     */
    public static function findByPostCode(string $postCode): ?static
    {
        return static::query()->postCode($postCode)->first();
    }


    /**
     * Find a commune by its slug.
     * Returns null if not found.
     *
     * @param  string  $slug  The Commune Slug.
     * @return static|null
     */
    public static function findBySlug(string $slug): ?static
    {
        return static::query()->slug($slug)->first();
    }



    //--------------------------------------------------------------------------
    // Scopes
    //--------------------------------------------------------------------------

    /**
     * Get the city (wilaya) that this commune belongs to.
     *
     * @return BelongsTo
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'wilaya_id');
    }

    /**
     * Scope a query to search communes by name (English or Arabic).
     *
     * @param  Builder  $query
     * @param  string  $term  Search term.
     * @return Builder
     */
    public function scopeSearchByName(Builder $query, string $term): Builder
    {
        $term = trim($term);
        if (empty($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'LIKE', "%{$term}%")
                ->orWhere('arabic_name', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Scope a query to filter communes by postal code.
     *
     * @param  Builder  $query
     * @param  string  $postCode  The postal code.
     * @return Builder
     */
    public function scopePostCode(Builder $query, string $postCode): Builder
    {
        return $query->where('post_code', $postCode);
    }


    /**
     * Scope a query to filter communes by slug.
     *
     * @param  Builder  $query
     * @param  string  $slug  The slug.
     * @return Builder
     */
    public function scopeSlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', $slug);
    }


    //--------------------------------------------------------------------------
    // Helper Methods
    //--------------------------------------------------------------------------

    /**
     * Scope a query to find communes within a given radius (using Haversine formula).
     *
     * @param  Builder  $query
     * @param  float  $latitude  Latitude of the center point.
     * @param  float  $longitude  Longitude of the center point.
     * @param  int  $radius  Radius distance.
     * @param  string  $unit  Unit of distance ('km' or 'miles'). Defaults to 'km'.
     * @return Builder
     */
    public function scopeWithinRadius(
        Builder $query,
        float $latitude,
        float $longitude,
        int $radius = 10,
        string $unit = 'km'
    ): Builder {
        $earthRadius = ($unit === 'miles') ? 3959 : 6371;

        $haversine = sprintf(
            '(%d * ACOS(COS(RADIANS(%f)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(%f)) + SIN(RADIANS(%f)) * SIN(RADIANS(latitude))))',
            $earthRadius,
            $latitude,
            $longitude,
            $latitude
        );

        return $query
            ->select('*')
            ->selectRaw("{$haversine} AS distance")
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            // Use whereRaw instead of havingRaw for SQLite compatibility
            ->whereRaw("{$haversine} <= ?", [$radius])
            ->orderBy('distance', 'asc');
    }
    /**
     * Get the coordinates as an associative array.
     * Returns null if latitude or longitude is missing.
     *
     * @return array{latitude: float, longitude: float}|null
     */
    public function getCoordinates(): ?array
    {
        if (is_null($this->latitude) || is_null($this->longitude)) {
            return null;
        }

        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }

    /**
     * Get a display name, optionally preferring Arabic.
     *
     * @param  bool  $preferArabic
     * @return string
     */
    public function getDisplayName(bool $preferArabic = false): string
    {
        return $preferArabic ? $this->arabic_name : $this->name;
    }
}