<?php

namespace App\Models\Purchases;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING_APPROVAL = 'pending_approval';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_SENT = 'sent';
    public const STATUS_PARTIALLY_RECEIVED = 'partially_received';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'po_number',
        'supplier_reference',
        'supplier_id',
        'request_id',
        'order_date',
        'expected_delivery_date',
        'received_date',
        'status',
        'payment_status',
        'currency',
        'exchange_rate',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'shipping_cost',
        'total_amount',
        'paid_amount',
        'payment_terms',
        'shipping_method',
        'delivery_address',
        'terms_conditions',
        'notes',
        'internal_notes',
        'created_by',
        'approved_by',
        'approved_at',
        'sent_at',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'received_date' => 'date',
        'approved_at' => 'datetime',
        'sent_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'exchange_rate' => 'decimal:6',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($model->po_number) {
                return;
            }

            $year = date('Y');
            $prefix = "PO-{$year}-";

            $last = static::withTrashed()
                ->where('po_number', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderByDesc('po_number')
                ->value('po_number');

            $next = $last ? ((int) substr($last, -5)) + 1 : 1;
            $model->po_number = $prefix . str_pad($next, 5, '0', STR_PAD_LEFT);
        });
    }

    // Relationships
    public function supplier()
    {
        return $this->belongsTo(\App\Models\MasterData\Supplier::class);
    }

    public function request()
    {
        return $this->belongsTo(PurchaseRequest::class, 'request_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    // Helpers
    public function getBalanceDueAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PENDING_APPROVAL]);
    }

    public function recalculateTotals(): void
    {
        $this->subtotal = $this->items->sum('line_total');
        $this->total_amount = $this->subtotal - $this->discount_amount
            + $this->tax_amount + $this->shipping_cost;
        $this->save();
    }
}