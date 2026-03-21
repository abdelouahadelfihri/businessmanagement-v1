<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryLine extends Model
{
    protected $fillable = [
        'delivery_id',
        'product_id',
        'quantity',
    ];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}