<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    public function delivery()
    {
        return $this->belongsTo(SaleDelivery::class, 'delivery_id');
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\MasterData\Product::class, 'product_id');
    }
}