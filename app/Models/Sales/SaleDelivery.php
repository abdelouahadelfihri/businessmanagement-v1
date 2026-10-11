<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MasterData\Warehouse;
use App\Models\MasterData\StockMovement;

class SaleDelivery extends Model
{
    use HasFactory;

    protected $table = 'sales_deliveries';

    public const STATUSES = [
        'draft' => 'Draft',
        'validated' => 'Validated',
        'cancelled' => 'Cancelled',
    ];

    public const STATUS_COLORS = [
        'draft' => 'secondary',
        'validated' => 'success',
        'cancelled' => 'danger',
    ];

    protected $fillable = [
        'sales_order_id',
        'delivery_number',
        'date',
        'status',
        'warehouse_id',
        'currency',
        'carrier',
        'tracking_number',
        'shipping_address',
        'recipient_name',
        'recipient_phone',
        'notes',
        'internal_notes',
        'subtotal',
        'tax',
        'total',
        'validated_at',
        'cancelled_at',
        'cancellation_reason',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'validated_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Sales order
    public function salesOrder()
    {
        return $this->belongsTo(SaleOrder::class, 'sales_order_id');
    }

    // Warehouse
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    // Lines
    public function lines()
    {
        return $this->hasMany(SaleDeliveryLine::class, 'sale_delivery_id');
    }

    // Stock movements
    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'source');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /** DN-2026-0001, DN-2026-0002 ... (restarts every year) */
    public static function generateNumber(): string
    {
        $prefix = 'DN-' . now()->format('Y') . '-';

        $last = static::where('delivery_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('delivery_number');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
