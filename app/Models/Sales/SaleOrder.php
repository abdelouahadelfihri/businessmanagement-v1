<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleOrder extends Model
{
    use HasFactory;

    protected $table = 'sales_orders';

    public const STATUSES = [
        'draft' => 'Draft',
        'confirmed' => 'Confirmed',
        'processing' => 'Processing',
        'delivered' => 'Delivered',
        'invoiced' => 'Invoiced',
        'cancelled' => 'Cancelled',
    ];

    public const STATUS_COLORS = [
        'draft' => 'secondary',
        'confirmed' => 'primary',
        'processing' => 'info',
        'delivered' => 'success',
        'invoiced' => 'dark',
        'cancelled' => 'danger',
    ];

    protected $fillable = [
        'customer_id',
        'quotation_id',
        'order_number',
        'reference',
        'currency',
        'date',
        'expected_delivery_date',
        'payment_terms',
        'delivery_terms',
        'shipping_address',
        'billing_address',
        'notes',
        'terms_conditions',
        'internal_notes',
        'subtotal',
        'discount_amount',
        'tax',
        'shipping_cost',
        'total',
        'status',
        'confirmed_at',
        'delivered_at',
        'cancelled_at',
        'cancellation_reason',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'expected_delivery_date' => 'date',
        'confirmed_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /* ---------------- Relations ---------------- */

    public function customer()
    {
        return $this->belongsTo(\App\Models\MasterData\Customer::class, 'customer_id', 'id');
    }

    public function quotation()
    {
        return $this->belongsTo(\App\Models\Sales\SaleQuotation::class, 'quotation_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(\App\Models\Sales\SaleOrderLine::class, 'order_id');
    }

    /** Alias of items(), used by the delivery, invoice and return controllers. */
    public function lines()
    {
        return $this->items();
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /* ---------------- Accessors ---------------- */

    /** Expected delivery date passed and the order is still not delivered. */
    public function getIsLateAttribute(): bool
    {
        return $this->expected_delivery_date
            && $this->expected_delivery_date->isPast()
            && !$this->expected_delivery_date->isToday()
            && in_array($this->status, ['confirmed', 'processing'], true);
    }

    /* ---------------- Helpers ---------------- */

    /** SO-2026-0001, SO-2026-0002 ... (restarts every year) */
    public static function generateNumber(): string
    {
        $prefix = 'SO-' . now()->format('Y') . '-';

        $last = static::where('order_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('order_number');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
