<?php

namespace App\Models\Sales;

use App\Models\MasterData\Product;
use Illuminate\Database\Eloquent\Model;

class SalesInvoiceLine extends Model
{
    protected $table = 'sales_invoice_lines';

    protected $fillable = [
        'sales_invoice_id', 'order_line_id', 'product_id', 'description', 'unit',
        'quantity', 'unit_price', 'discount_percent', 'tax_rate', 'tax_amount',
        'total_price', 'notes', 'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function invoice()
    {
        // explicit FK: Laravel would otherwise guess `sale_invoice_id`
        return $this->belongsTo(SaleInvoice::class, 'sales_invoice_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
