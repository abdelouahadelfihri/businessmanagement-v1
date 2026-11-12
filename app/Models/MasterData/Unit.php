<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $table = 'units';
    protected $primaryKey = 'unit_id';

    public const TYPES = [
        'count' => ['label' => 'Count / pieces', 'icon' => 'bi-123'],
        'weight' => ['label' => 'Weight', 'icon' => 'bi-speedometer'],
        'volume' => ['label' => 'Volume', 'icon' => 'bi-droplet'],
        'length' => ['label' => 'Length', 'icon' => 'bi-rulers'],
        'area' => ['label' => 'Area', 'icon' => 'bi-bounding-box'],
        'time' => ['label' => 'Time', 'icon' => 'bi-clock'],
    ];

    /**
     * Standard units offered with "Add standard units" (name, abbreviation, type,
     * base abbreviation, factor, decimals). Base units come first.
     */
    public const STANDARD = [
        ['Piece', 'pc', 'count', null, 1, 0],
        ['Box', 'box', 'count', 'pc', 12, 0],
        ['Pack', 'pack', 'count', 'pc', 6, 0],
        ['Dozen', 'dz', 'count', 'pc', 12, 0],
        ['Kilogram', 'kg', 'weight', null, 1, 3],
        ['Gram', 'g', 'weight', 'kg', 0.001, 0],
        ['Ton', 't', 'weight', 'kg', 1000, 3],
        ['Liter', 'L', 'volume', null, 1, 3],
        ['Milliliter', 'mL', 'volume', 'L', 0.001, 0],
        ['Meter', 'm', 'length', null, 1, 2],
        ['Centimeter', 'cm', 'length', 'm', 0.01, 0],
        ['Square meter', 'm²', 'area', null, 1, 2],
        ['Hour', 'h', 'time', null, 1, 2],
        ['Working day', 'day', 'time', 'h', 8, 1],
    ];

    protected $fillable = [
        'name', 'abbreviation', 'type', 'base_unit_id', 'conversion_factor',
        'decimal_places', 'description', 'is_active',
    ];

    protected $casts = [
        'conversion_factor' => 'float',
        'decimal_places' => 'integer',
        'is_active' => 'boolean',
    ];

    /* ---------------- Relations ---------------- */

    public function baseUnit()
    {
        return $this->belongsTo(Unit::class, 'base_unit_id', 'unit_id');
    }

    /** Units defined as a multiple / fraction of this one. */
    public function derivedUnits()
    {
        return $this->hasMany(Unit::class, 'base_unit_id', 'unit_id')->orderBy('conversion_factor');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'unit_id', 'unit_id');
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
                ->orWhere('abbreviation', 'like', $like)
                ->orWhere('description', 'like', $like);
        });
    }

    /* ---------------- Helpers ---------------- */

    public function getLabelAttribute(): string
    {
        return $this->abbreviation ? "{$this->name} ({$this->abbreviation})" : $this->name;
    }

    public function getShortAttribute(): string
    {
        return $this->abbreviation ?: $this->name;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type]['label'] ?? ucfirst((string) $this->type);
    }

    /** Factor to the root unit of the chain (box -> pc: 12; g -> kg: 0.001). */
    public function factorToRoot(): float
    {
        $factor = 1.0;
        $unit = $this;
        $guard = 0;

        while ($unit->baseUnit && $guard++ < 10) {
            $factor *= (float) $unit->conversion_factor;
            $unit = $unit->baseUnit;
        }

        return $factor;
    }

    public function rootUnit(): Unit
    {
        $unit = $this;
        $guard = 0;
        while ($unit->baseUnit && $guard++ < 10) {
            $unit = $unit->baseUnit;
        }

        return $unit;
    }

    /** Converts a quantity of this unit into $target (same type). Null when not convertible. */
    public function convert(float $quantity, Unit $target): ?float
    {
        if ($this->type !== $target->type || $this->rootUnit()->unit_id !== $target->rootUnit()->unit_id) {
            return null;
        }

        return $quantity * $this->factorToRoot() / $target->factorToRoot();
    }

    /** "1 box = 12 pc" */
    public function getConversionTextAttribute(): ?string
    {
        if (! $this->baseUnit) {
            return null;
        }

        return '1 ' . $this->short . ' = ' . self::number($this->conversion_factor) . ' ' . $this->baseUnit->short;
    }

    /** IDs of this unit and every unit derived from it (to prevent cycles). */
    public function descendantIds(): array
    {
        $ids = [$this->unit_id];
        $level = [$this->unit_id];

        for ($depth = 0; $level && $depth < 10; $depth++) {
            $level = static::whereIn('base_unit_id', $level)->pluck('unit_id')->all();
            $ids = array_merge($ids, $level);
        }

        return array_values(array_unique($ids));
    }

    public static function number(float $value): string
    {
        return rtrim(rtrim(number_format($value, 6, '.', ''), '0'), '.') ?: '0';
    }
}
