<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceiptLine extends Model
{
    protected $fillable = [
        'receipt_id',
        'product_id',
        'quantity',
    ];

    public function receipt()
    {
        return $this->belongsTo(GoodsReceipt::class, 'receipt_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

}