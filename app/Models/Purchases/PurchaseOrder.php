<?php

namespace App\Models\Purchases;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'request_id',
        'order_date',
        'status',
        'total_amount',
    ];

    /**
     * Relationships
     */

    public function supplier()
    {
        return $this->belongsTo(\App\Models\Purchases\Supplier::class);
    }

    public function request()
    {
        return $this->belongsTo(\App\Models\Purchases\PurchaseRequest::class, 'request_id');
    }
}