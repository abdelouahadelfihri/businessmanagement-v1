<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_order_id',
        'customer_id',
        'return_number',
        'date',
        'reason',
        'subtotal',
        'tax',
        'total',
        'status',
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Models\MasterData\Customer::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(\App\Models\Sales\SaleOrder::class);
    }
}