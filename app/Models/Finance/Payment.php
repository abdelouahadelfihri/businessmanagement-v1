<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payable_id',
        'payable_type',
        'payment_date',
        'amount',
        'payment_method',
        'reference',
    ];

    /**
     * Polymorphic relation
     * A payment belongs to either:
     * - SalesInvoice
     * - SupplierInvoice
     */
    public function payable()
    {
        return $this->morphTo();
    }
}