<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Warehouse extends Model
{
    use HasFactory;

    protected $table = 'warehouses';

    public const TYPES = [
        'main' => ['label' => 'Main warehouse', 'icon' => 'bi-buildings', 'color' => 'primary'],
        'secondary' => ['label' => 'Secondary warehouse', 'icon' => 'bi-building', 'color' => 'info'],
        'store' => ['label' => 'Shop / store', 'icon' => 'bi-shop', 'color' => 'success'],
        'transit' => ['label' => 'Transit', 'icon' => 'bi-truck', 'color' => 'warning'],
        'returns' => ['label' => 'Returns / quarantine', 'icon' => 'bi-arrow-return-left', 'color' => 'danger'],
    ];

    /** Tables and columns that point to a warehouse (used to block deletion). */
    public const REFERENCES = [
        'stock_movements' => ['warehouse_id', 'source_warehouse_id'],
        'transfers' => ['from_warehouse_id', 'to_warehouse_id'],
        'purchase_receipts' => ['warehouse_id'],
        'purchase_receipt_lines' => ['warehouse_id'],
        'purchase_invoices' => ['warehouse_id'],
        'purchase_returns' => ['warehouse_id'],
        'sales_deliveries' => ['warehouse_id'],
        'sales_returns' => ['warehouse_id'],
        'sale_returns' => ['warehouse_id'],
    ];

    protected $fillable = [
        'code', 'name', 'type', 'is_default', 'location_id',
        'manager_name', 'phone', 'email', 'address', 'capacity_m2',
        'is_refrigerated', 'min_temperature', 'max_temperature',
        'notes', 'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_refrigerated' => 'boolean',
        'is_active' => 'boolean',
        'capacity_m2' => 'decimal:2',
        'min_temperature' => 'decimal:1',
        'max_temperature' => 'decimal:1',
    ];

    protected static function booted(): void
    {
        static::created(function (Warehouse $warehouse) {
            if (blank($warehouse->code)) {
                $warehouse->code = sprintf('WH-%03d', $warehouse->id);
                $warehouse->saveQuietly();
            }
        });

        // Only one default warehouse; the first one becomes default automatically
        static::saving(function (Warehouse $warehouse) {
            if (! $warehouse->exists && ! static::where('is_default', true)->exists()) {
                $warehouse->is_default = true;
            }
            if (! $warehouse->is_refrigerated) {
                $warehouse->min_temperature = null;
                $warehouse->max_temperature = null;
            }
        });
        static::saved(function (Warehouse $warehouse) {
            if ($warehouse->is_default) {
                static::whereKeyNot($warehouse->id)->where('is_default', true)->update(['is_default' => false]);
            }
        });
    }

    /* ---------------- Relations ---------------- */

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id', 'location_id');
    }

    public function stocks()
    {
        return $this->hasMany(WarehouseStock::class);
    }

    /* ---------------- Scopes ---------------- */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Default warehouse first, then by name. */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('is_default')->orderBy('name');
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
                ->orWhere('manager_name', 'like', $like)
                ->orWhere('address', 'like', $like)
                ->orWhereHas('location', fn($l) => $l->where('name', 'like', $like)->orWhere('address', 'like', $like));
        });
    }

    /* ---------------- Helpers ---------------- */

    public function getTypeMetaAttribute(): array
    {
        return self::TYPES[$this->type] ?? ['label' => ucfirst((string) $this->type), 'icon' => 'bi-building', 'color' => 'secondary'];
    }

    /** Own address, else the location's address. */
    public function getFullAddressAttribute(): ?string
    {
        return $this->address ?: $this->location?->address;
    }

    public function getTemperatureRangeAttribute(): ?string
    {
        if (! $this->is_refrigerated) {
            return null;
        }
        $min = $this->min_temperature !== null ? (float) $this->min_temperature . ' °C' : null;
        $max = $this->max_temperature !== null ? (float) $this->max_temperature . ' °C' : null;

        return $min && $max ? "$min to $max" : ($min ? "from $min" : ($max ? "up to $max" : 'Refrigerated'));
    }

    /** True when the warehouse holds stock or appears in any movement / document. */
    public function isUsed(): bool
    {
        if ($this->stocks()->where('quantity', '!=', 0)->exists()) {
            return true;
        }

        foreach (self::REFERENCES as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            foreach ($columns as $column) {
                if (Schema::hasColumn($table, $column) && DB::table($table)->where($column, $this->id)->exists()) {
                    return true;
                }
            }
        }

        return false;
    }
}
