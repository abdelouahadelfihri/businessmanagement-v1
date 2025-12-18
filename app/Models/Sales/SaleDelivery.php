<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleDelivery extends Model
{
    use HasFactory;

    protected $table = 'sales_deliveries';

    protected $fillable = [
        'sales_order_id',
        'delivery_number',
        'date',
        'status',
        'total'
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SaleOrder::class, 'sales_order_id');
    }
}