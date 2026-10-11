<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use App\Models\MasterData\Product;

class SaleDeliveryLine extends Model
{
    protected $table = 'sale_delivery_lines';

    protected $fillable = [
        'sale_delivery_id',
        'order_line_id',
        'product_id',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'discount_percent',
        'tax_rate',
        'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function delivery()
    {
        return $this->belongsTo(SaleDelivery::class, 'sale_delivery_id');
    }

    /** The sales order line this delivery line fulfils. */
    public function orderLine()
    {
        return $this->belongsTo(SaleOrderLine::class, 'order_line_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
