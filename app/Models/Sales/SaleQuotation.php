<?php

namespace App\Models\Sales;

use App\Models\MasterData\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleQuotation extends Model
{
    use HasFactory;

    protected $table = 'sales_quotes';

    public const STATUSES = [
        'draft'     => 'Draft',
        'sent'      => 'Sent',
        'accepted'  => 'Accepted',
        'rejected'  => 'Rejected',
        'cancelled' => 'Cancelled',
    ];

    protected $fillable = [
        'quote_number', 'customer_id', 'reference', 'date', 'valid_until', 'status', 'currency',
        'payment_terms', 'delivery_terms',
        'subtotal', 'discount_amount', 'tax', 'shipping_cost', 'total',
        'notes', 'terms_conditions', 'internal_notes',
        'created_by', 'sent_at', 'accepted_at', 'rejected_at', 'rejection_reason',
    ];

    protected $casts = [
        'date'            => 'date',
        'valid_until'     => 'date',
        'sent_at'         => 'datetime',
        'accepted_at'     => 'datetime',
        'rejected_at'     => 'datetime',
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax'             => 'decimal:2',
        'shipping_cost'   => 'decimal:2',
        'total'           => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->quote_number)) {
                $nextId = (static::max('id') ?? 0) + 1;
                $model->quote_number = 'QT-' . date('Y') . '-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(SaleQuotationLine::class, 'quote_id')->orderBy('sort_order')->orderBy('id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** A quote that was sent but whose validity date has passed without an answer. */
    public function getIsExpiredAttribute(): bool
    {
        return $this->status === 'sent'
            && $this->valid_until
            && $this->valid_until->copy()->endOfDay()->isPast();
    }
}
