<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $table = 'locations';
    protected $primaryKey = 'location_id';

    public const TYPES = [
        'head_office' => ['label' => 'Head office', 'icon' => 'bi-building-fill', 'color' => 'primary'],
        'warehouse_site' => ['label' => 'Warehouse site', 'icon' => 'bi-buildings', 'color' => 'info'],
        'factory' => ['label' => 'Factory', 'icon' => 'bi-gear-wide-connected', 'color' => 'warning'],
        'shop' => ['label' => 'Shop', 'icon' => 'bi-shop', 'color' => 'success'],
        'office' => ['label' => 'Office', 'icon' => 'bi-briefcase', 'color' => 'secondary'],
        'other' => ['label' => 'Other', 'icon' => 'bi-geo-alt', 'color' => 'dark'],
    ];

    protected $fillable = [
        'code', 'name', 'type', 'address', 'city', 'postal_code', 'country',
        'latitude', 'longitude', 'contact_name', 'phone', 'email', 'notes', 'is_active',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::created(function (Location $location) {
            if (blank($location->code)) {
                $location->code = sprintf('LOC-%03d', $location->location_id);
                $location->saveQuietly();
            }
        });
    }

    /* ---------------- Relations ---------------- */

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class, 'location_id', 'location_id');
    }

    /* ---------------- Scopes ---------------- */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $like = '%' . $term . '%';
            $q->where('name', 'like', $like)
                ->orWhere('code', 'like', $like)
                ->orWhere('address', 'like', $like)
                ->orWhere('city', 'like', $like)
                ->orWhere('contact_name', 'like', $like);
        });
    }

    /* ---------------- Helpers ---------------- */

    public function getTypeMetaAttribute(): array
    {
        return self::TYPES[$this->type] ?? self::TYPES['other'];
    }

    public function getFullAddressAttribute(): ?string
    {
        $cityLine = trim(($this->postal_code ? $this->postal_code . ' ' : '') . ($this->city ?? ''));
        $parts = array_filter([$this->address, $cityLine, $this->country]);

        return $parts ? implode("\n", $parts) : null;
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /** Google Maps link: exact coordinates when known, else an address search. */
    public function getMapUrlAttribute(): ?string
    {
        if ($this->hasCoordinates()) {
            return 'https://www.google.com/maps?q=' . $this->latitude . ',' . $this->longitude;
        }
        if ($this->full_address) {
            return 'https://www.google.com/maps/search/?api=1&query=' . urlencode(str_replace("\n", ', ', $this->full_address));
        }

        return null;
    }
}
