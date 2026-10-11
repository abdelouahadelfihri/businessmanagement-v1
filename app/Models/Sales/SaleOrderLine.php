<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;

class SaleOrderLine extends Model
{
    protected $table = 'sales_order_lines';

    protected $fillable = [
        'order_id',
        'product_id',
        'description',
        'quantity',
        'delivered_quantity',
        'invoiced_quantity',
        'unit',
        'price',
        'discount_percent',
        'tax_rate',
        'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'delivered_quantity' => 'decimal:3',
        'invoiced_quantity' => 'decimal:3',
        'price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(SaleOrder::class, 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\MasterData\Product::class, 'product_id');
    }
}
