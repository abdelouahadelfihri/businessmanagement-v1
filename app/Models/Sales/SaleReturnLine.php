<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleReturnLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_return_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal'
    ];

    public function salesReturn()
    {
        return $this->belongsTo(SaleReturn::class);
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\MasterData\Product::class);
    }
}