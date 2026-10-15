<?php

namespace App\Models\Sales;

use App\Models\MasterData\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleInvoice extends Model
{
    use HasFactory;
    protected $table = 'sales_invoices';

    public const STATUSES = [
        'draft' => 'Draft',
        'issued' => 'Issued',
        'partial' => 'Partially paid',
        'paid' => 'Paid',
        'cancelled' => 'Cancelled',
    ];

    public const STATUS_COLORS = [
        'draft' => 'secondary',
        'issued' => 'primary',
        'partial' => 'warning',
        'paid' => 'success',
        'cancelled' => 'danger',
    ];

    protected $fillable = [
        'sales_order_id', 'customer_id', 'invoice_number', 'reference', 'date', 'due_date',
        'currency', 'payment_terms', 'payment_method', 'billing_address',
        'subtotal', 'discount_total', 'tax', 'total', 'amount_paid', 'balance_due',
        'status', 'notes', 'internal_notes', 'created_by',
        'issued_at', 'paid_at', 'cancelled_at', 'cancellation_reason',
    ];

    protected $casts = [
        'date' => 'date',
        'due_date' => 'date',
        'issued_at' => 'datetime',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(SaleOrder::class, 'sales_order_id');
    }

    public function lines()
    {
        return $this->hasMany(SalesInvoiceLine::class, 'sales_invoice_id')->orderBy('sort_order')->orderBy('id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isOverdue(): bool
    {
        return in_array($this->status, ['issued', 'partial'], true)
            && $this->due_date
            && $this->due_date->lt(today());
    }

    /** Next invoice number: INV-YYYY-00001 */
    public static function generateNumber(): string
    {
        $prefix = 'INV-' . now()->format('Y') . '-';

        $last = static::where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
