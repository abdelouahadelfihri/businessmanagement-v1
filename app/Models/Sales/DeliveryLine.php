<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use App\Models\MasterData\Product;
use App\Models\Sales\SaleDelivery;

class DeliveryLine extends Model
{
    protected $fillable = [
        'delivery_id',
        'product_id',
        'quantity',
    ];

    public function delivery()
    {
        return $this->belongsTo(SaleDelivery::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}