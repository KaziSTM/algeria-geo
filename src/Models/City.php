<?php

namespace KaziSTM\AlgeriaGeo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Represents an Algerian City (Wilaya).
 *
 * @property int $id
 * @property int $code The official wilaya code.
 * @property string $name The name of the city (wilaya).
 * @property string $arabic_name The Arabic name of the city (wilaya).
 * @property float|null $longitude
 * @property float|null $latitude
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Commune[] $communes
 * @property-read int|null $communes_count
 *
 * @method static Builder|City searchByName(string $term) Scope to search cities by name (English or Arabic).
 * @method static Builder|City code(int $code) Scope to filter cities by their official code.
 * @method static Builder|City findNyCode(int $code) Find a city by its official code.
 *
 * ```
 */
class City extends Model
{
    use HasFactory;


    protected $fillable = [
        'id',
        'code',
        'name',
        'arabic_name',
        'longitude',
        'latitude',
    ];

    protected $casts = [
        'longitude' => 'float',
        'latitude' => 'float',
    ];

    /**
     * Find a city by its official code.
     * Returns null if not found.
     *
     * @param  int  $code  The Wilaya code.
     * @return static|null
     */
    public static function findByCode(int $code): ?static
    {
        return static::code($code)->first();
    }

    //--------------------------------------------------------------------------
    // Scopes
    //--------------------------------------------------------------------------

    /**
     * Get the communes belonging to this city (wilaya).
     *
     * @return HasMany
     */
    public function communes(): HasMany
    {
        return $this->hasMany(Commune::class, 'wilaya_id');
    }

    /**
     * Scope a query to search cities by name (English or Arabic).
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

    //--------------------------------------------------------------------------
    // Helper Methods
    //--------------------------------------------------------------------------

    /**
     * Scope a query to filter cities by their official code.
     *
     * @param  Builder  $query
     * @param  int  $code  The Wilaya code.
     * @return Builder
     */
    public function scopeCode(Builder $query, int $code): Builder
    {
        return $query->where('code', $code);
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