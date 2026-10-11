<?php

namespace App\Models\Sales;

use App\Models\MasterData\Product;
use Illuminate\Database\Eloquent\Model;

class SaleQuotationLine extends Model
{
    protected $table = 'sales_quote_lines';

    protected $fillable = [
        'quote_id', 'product_id', 'description', 'quantity', 'unit',
        'price', 'discount_percent', 'tax_rate', 'total', 'sort_order',
    ];

    protected $casts = [
        'quantity'         => 'decimal:3',
        'price'            => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'tax_rate'         => 'decimal:2',
        'total'            => 'decimal:2',
    ];

    public function quote()
    {
        return $this->belongsTo(SaleQuotation::class, 'quote_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
