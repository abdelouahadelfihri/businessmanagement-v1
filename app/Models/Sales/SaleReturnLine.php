<?php

namespace App\Models\Sales;

use App\Models\MasterData\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleReturnLine extends Model
{
    use HasFactory;

    protected $table = 'sales_return_lines';

    protected $fillable = [
        'sale_return_id', 'order_line_id', 'product_id', 'description', 'unit',
        'quantity', 'unit_price', 'discount_percent', 'tax_rate', 'tax_amount',
        'subtotal', 'total', 'condition', 'restock', 'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'restock' => 'boolean',
    ];

    public function salesReturn()
    {
        // explicit FK: Laravel would otherwise guess `sales_return_id`
        return $this->belongsTo(SaleReturn::class, 'sale_return_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
