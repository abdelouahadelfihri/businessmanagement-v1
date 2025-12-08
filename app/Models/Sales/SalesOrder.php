<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    use HasFactory;

    protected $table = 'sales_orders';

    protected $fillable = [
        'customer_id',
        'order_number',
        'date',
        'total',
        'status'
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Models\Purchase\Customer::class, 'customer_id', 'customerId');
    }
}