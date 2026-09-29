<?php

namespace App\Models\Purchases;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderLine extends Model
{
    use HasFactory;

    protected $table = 'purchase_order_lines';

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'description',
        'quantity',
        'received_quantity',
        'unit',
        'unit_price',
        'discount_percent',
        'tax_rate',
        'line_total',
    ];

    protected $casts = [
        'quantity'          => 'decimal:3',
        'received_quantity' => 'decimal:3',
        'unit_price'        => 'decimal:2',
        'discount_percent'  => 'decimal:2',
        'tax_rate'          => 'decimal:2',
        'line_total'        => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        // Always keep line_total consistent (before tax, after line discount)
        static::saving(function ($line) {
            $gross = (float) $line->quantity * (float) $line->unit_price;
            $line->line_total = round($gross * (1 - ((float) $line->discount_percent / 100)), 2);
        });

        // Keep the PO totals in sync
        static::saved(fn ($line) => $line->purchaseOrder?->load('items')->recalculateTotals());
        static::deleted(fn ($line) => $line->purchaseOrder?->load('items')->recalculateTotals());
    }

    /**
     * Relationships
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\MasterData\Product::class, 'product_id');
    }

    /**
     * Helpers
     */
    public function getTaxAmountAttribute()
    {
        return round($this->line_total * ((float) $this->tax_rate / 100), 2);
    }

    public function getRemainingQuantityAttribute()
    {
        return $this->quantity - $this->received_quantity;
    }

    public function isFullyReceived(): bool
    {
        return $this->received_quantity >= $this->quantity;
    }
}