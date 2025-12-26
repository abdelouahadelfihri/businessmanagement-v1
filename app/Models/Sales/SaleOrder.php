<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleOrder extends Model
{
    use HasFactory;

    protected $table = 'sales_orders';

    protected $fillable = [
        'customer_id',
        'quotation_id',
        'order_number',
        'date',
        'total',
        'status'
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Models\MasterData\Customer::class, 'customer_id', 'id');
    }

    public function quotation()
    {
        return $this->belongsTo(\App\Models\Sales\SaleQuotation::class, 'quotation_id', 'id');
    }
}