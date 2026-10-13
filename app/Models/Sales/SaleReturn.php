<?php

namespace App\Models\Sales;

use App\Models\MasterData\Customer;
use App\Models\MasterData\StockMovement;
use App\Models\MasterData\Warehouse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{
    use HasFactory;

    protected $table = 'sales_returns';

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

    public const REASONS = [
        'defective' => 'Defective product',
        'wrong_item' => 'Wrong item delivered',
        'not_as_described' => 'Not as described',
        'damaged_in_transit' => 'Damaged in transit',
        'customer_changed_mind' => 'Customer changed mind',
        'other' => 'Other',
    ];

    public const CONDITIONS = [
        'good' => 'Good',
        'damaged' => 'Damaged',
        'expired' => 'Expired',
        'other' => 'Other',
    ];

    public const REFUND_METHODS = [
        'Credit note / customer balance',
        'Bank transfer',
        'Cash',
        'Exchange',
        'No refund',
    ];

    protected $fillable = [
        'sales_order_id', 'customer_id', 'invoice_id', 'return_number', 'reference', 'date',
        'warehouse_id', 'currency', 'reason_type', 'reason', 'refund_method',
        'subtotal', 'discount_total', 'tax', 'total', 'status',
        'notes', 'internal_notes', 'created_by',
        'validated_at', 'cancelled_at', 'cancellation_reason',
    ];

    protected $casts = [
        'date' => 'date',
        'validated_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(SaleOrder::class, 'sales_order_id');
    }

    public function invoice()
    {
        return $this->belongsTo(SaleInvoice::class, 'invoice_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function lines()
    {
        return $this->hasMany(SaleReturnLine::class, 'sale_return_id')->orderBy('sort_order')->orderBy('id');
    }

    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'source');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /** Next return number: RET-YYYY-00001 */
    public static function generateNumber(): string
    {
        $prefix = 'RET-' . now()->format('Y') . '-';

        $last = static::where('return_number', 'like', $prefix . '%')
            ->orderByDesc('return_number')
            ->value('return_number');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
