<?php

namespace App\Models\Purchases;

use App\Models\MasterData\StockMovement;
use App\Models\MasterData\Supplier;
use App\Models\MasterData\Warehouse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoice extends Model
{
    use HasFactory;

    public const STATUSES = [
        'draft'     => 'Draft',
        'validated' => 'Validated',
        'cancelled' => 'Cancelled',
    ];

    public const PAYMENT_STATUSES = [
        'unpaid'  => 'Unpaid',
        'partial' => 'Partial',
        'paid'    => 'Paid',
    ];

    public const PAYMENT_METHODS = [
        'bank_transfer' => 'Bank transfer',
        'cash'          => 'Cash',
        'cheque'        => 'Cheque',
        'card'          => 'Card',
        'other'         => 'Other',
    ];

    protected $fillable = [
        'purchase_order_id',
        'purchase_receipt_id',
        'supplier_id',
        'warehouse_id',
        'invoice_number',
        'supplier_invoice_number',
        'date',
        'due_date',
        'payment_terms',
        'currency',
        'subtotal',
        'discount_amount',
        'tax',
        'shipping_cost',
        'total',
        'amount_paid',
        'payment_status',
        'payment_method',
        'paid_at',
        'status',
        'notes',
        'internal_notes',
        'created_by',
        'validated_by',
        'validated_at',
    ];

    protected $casts = [
        'date'            => 'date',
        'due_date'        => 'date',
        'paid_at'         => 'date',
        'validated_at'    => 'datetime',
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax'             => 'decimal:2',
        'shipping_cost'   => 'decimal:2',
        'total'           => 'decimal:2',
        'amount_paid'     => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->invoice_number)) {
                $nextId = (static::max('id') ?? 0) + 1;
                $model->invoice_number = 'PI-' . date('Y') . '-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
            }
            if (empty($model->status)) {
                $model->status = 'draft';
            }
            if (empty($model->payment_status)) {
                $model->payment_status = 'unpaid';
            }
        });
    }

    /**
     * An invoice linked to a purchase order or a receipt does NOT move stock
     * (the goods receipt already did). Only a "direct" invoice does.
     */
    public function affectsStock(): bool
    {
        return empty($this->purchase_order_id) && empty($this->purchase_receipt_id);
    }

    public function getBalanceDueAttribute(): float
    {
        return round((float) $this->total - (float) $this->amount_paid, 2);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'validated'
            && $this->payment_status !== 'paid'
            && $this->due_date
            && $this->due_date->isPast();
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function purchaseReceipt()
    {
        return $this->belongsTo(PurchaseReceipt::class, 'purchase_receipt_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validatedBy()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseInvoiceLine::class, 'invoice_id');
    }

    // Kept for existing code that calls ->lines()
    public function lines()
    {
        return $this->items();
    }

    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'source');
    }
}
