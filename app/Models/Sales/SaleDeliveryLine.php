<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use App\Models\MasterData\Product;

class SaleDeliveryLine extends Model
{
    protected $fillable = [
        'sale_delivery_id',
        'product_id',
        'quantity',
        'unit_price',
    ];

    public function delivery()
    {
        return $this->belongsTo(SaleDelivery::class, 'sale_delivery_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}