<?php

namespace App\Models\Purchases;

use App\Models\MasterData\Product;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoiceLine extends Model
{
    protected $fillable = [
        'invoice_id',
        'purchase_order_line_id',
        'product_id',
        'description',
        'unit',
        'quantity',
        'price',
        'discount_percent',
        'tax_rate',
        'total',
    ];

    protected $casts = [
        'quantity'         => 'decimal:3',
        'price'            => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'tax_rate'         => 'decimal:2',
        'total'            => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'invoice_id');
    }

    public function orderLine()
    {
        return $this->belongsTo(PurchaseOrderLine::class, 'purchase_order_line_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
