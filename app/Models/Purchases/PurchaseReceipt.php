<?php

namespace App\Models\Purchases;

use App\Models\MasterData\StockMovement;
use App\Models\MasterData\Supplier;
use App\Models\MasterData\Warehouse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReceipt extends Model
{
    use HasFactory;

    public const STATUSES = [
        'draft'     => 'Draft',
        'validated' => 'Validated',
        'cancelled' => 'Cancelled',
    ];

    public const QUALITY_STATUSES = [
        'pending' => 'Pending',
        'passed'  => 'Passed',
        'partial' => 'Partially accepted',
        'failed'  => 'Rejected',
    ];

    protected $fillable = [
        'purchase_order_id',
        'supplier_id',
        'warehouse_id',
        'receipt_number',
        'date',
        'delivery_note_number',
        'carrier',
        'tracking_number',
        'currency',
        'quality_status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'shipping_cost',
        'total',
        'status',
        'notes',
        'internal_notes',
        'received_by',
        'validated_by',
        'validated_at',
    ];

    protected $casts = [
        'date'            => 'date',
        'validated_at'    => 'datetime',
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'shipping_cost'   => 'decimal:2',
        'total'           => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->receipt_number)) {
                $nextId = (static::max('id') ?? 0) + 1;
                $model->receipt_number = 'GR-' . date('Y') . '-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
            }
            if (empty($model->status)) {
                $model->status = 'draft';
            }
        });
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function validatedBy()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseReceiptLine::class, 'purchase_receipt_id');
    }

    // Kept for existing code that already calls ->lines()
    public function lines()
    {
        return $this->items();
    }

    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'source');
    }
}
