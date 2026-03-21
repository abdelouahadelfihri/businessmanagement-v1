<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesQuoteLine extends Model
{
    protected $fillable = [
        'quote_id',
        'product_id',
        'quantity',
        'price',
        'discount',
        'total',
    ];

    public function quote()
    {
        return $this->belongsTo(SalesQuote::class, 'quote_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}