<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    public const TYPES = [
        'stockable' => 'Stockable product',
        'consumable' => 'Consumable',
        'service' => 'Service',
    ];

    public const TAX_RATES = [0, 7, 10, 14, 20]; // Moroccan VAT rates

    protected $fillable = [
        'code', 'bar_code', 'type', 'name', 'description', 'brand',
        'category_id', 'unit_id',
        'purchase_price', 'sale_price', 'tax_rate',
        'track_stock', 'reorder_level', 'max_stock',
        'preferred_supplier_id', 'weight', 'notes', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'track_stock' => 'boolean',
        'reorder_level' => 'integer',
        'max_stock' => 'integer',
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'weight' => 'decimal:3',
    ];

    protected static function booted(): void
    {
        // Auto-assign a code (PRD-00001) when none was typed in
        static::created(function (Product $product) {
            if (blank($product->code)) {
                $product->code = sprintf('PRD-%05d', $product->id);
                $product->saveQuietly();
            }
        });

        // Services never carry stock
        static::saving(function (Product $product) {
            if ($product->type === 'service') {
                $product->track_stock = false;
            }
        });
    }

    /* ---------------- Relations ---------------- */

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'unit_id');
    }

    public function preferredSupplier()
    {
        return $this->belongsTo(Supplier::class, 'preferred_supplier_id');
    }

    public function warehouseStocks()
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
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
                ->orWhere('bar_code', 'like', $like)
                ->orWhere('brand', 'like', $like);
        });
    }

    /* ---------------- Helpers ---------------- */

    public function getCurrentStockAttribute(): float
    {
        return (float) $this->warehouseStocks()->sum('quantity');
    }

    /** Short unit label for documents (abbreviation, else name). */
    public function getUnitLabelAttribute(): ?string
    {
        return $this->unit?->abbreviation ?: $this->unit?->name;
    }

    /** Sale price including VAT. */
    public function getSalePriceTtcAttribute(): float
    {
        return round((float) $this->sale_price * (1 + (float) $this->tax_rate / 100), 2);
    }

    /** Gross margin in % of the sale price (null when no sale price). */
    public function getMarginPercentAttribute(): ?float
    {
        $sale = (float) $this->sale_price;

        return $sale > 0 ? round(($sale - (float) $this->purchase_price) / $sale * 100, 1) : null;
    }

    /** ok | low | out | none (stock not tracked) for a given stock level. */
    public function stockStatus(float $stock): string
    {
        if (! $this->track_stock) {
            return 'none';
        }
        if ($stock <= 0) {
            return 'out';
        }

        return $this->reorder_level > 0 && $stock <= $this->reorder_level ? 'low' : 'ok';
    }

    /** True when the product appears in any stock movement or document line. */
    public function isUsed(): bool
    {
        if ($this->stockMovements()->exists() || $this->warehouseStocks()->where('quantity', '!=', 0)->exists()) {
            return true;
        }

        $lineTables = [
            'sales_quote_lines', 'sales_order_lines', 'sales_invoice_lines', 'sales_delivery_lines', 'sales_return_lines',
            'sale_delivery_lines', 'sale_return_lines',
            'purchase_request_lines', 'purchase_order_lines', 'purchase_receipt_lines', 'purchase_invoice_lines', 'purchase_return_lines',
            'transfer_lines',
        ];

        foreach ($lineTables as $table) {
            if (Schema::hasTable($table) && DB::table($table)->where('product_id', $this->id)->exists()) {
                return true;
            }
        }

        return false;
    }
}
