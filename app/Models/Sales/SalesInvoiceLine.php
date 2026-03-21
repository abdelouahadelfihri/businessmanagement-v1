<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use App\Models\MasterData\Product;
use App\Models\Sales\SaleInvoice;


class SalesInvoiceLine extends Model
{
    protected $fillable = [
        'invoice_id',
        'product_id',
        'quantity',
        'price',
        'discount',
        'total',
    ];

    public function invoice()
    {
        return $this->belongsTo(SaleInvoice::class, 'invoice_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}