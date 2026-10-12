<?php

namespace App\Models\Sales;

use App\Models\MasterData\Product;
use Illuminate\Database\Eloquent\Model;

class SalesInvoiceLine extends Model
{
    protected $fillable = [
        'invoice_id', 'order_line_id', 'product_id', 'description', 'unit',
        'quantity', 'price', 'discount_percent', 'tax_rate', 'tax_amount',
        'total', 'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
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
