<?php

namespace App\Models\Purchases;

use App\Models\MasterData\Product;
use Illuminate\Database\Eloquent\Model;

class PurchaseReceiptLine extends Model
{
    protected $fillable = [
        'purchase_receipt_id',
        'purchase_order_line_id',
        'product_id',
        'description',
        'unit',
        'ordered_quantity',
        'received_quantity',
        'rejected_quantity',
        'unit_price',
        'discount_percent',
        'tax_rate',
        'total_price',
        'batch_number',
        'expiry_date',
    ];

    protected $casts = [
        'expiry_date'       => 'date',
        'ordered_quantity'  => 'decimal:3',
        'received_quantity' => 'decimal:3',
        'rejected_quantity' => 'decimal:3',
        'unit_price'        => 'decimal:2',
        'discount_percent'  => 'decimal:2',
        'tax_rate'          => 'decimal:2',
        'total_price'       => 'decimal:2',
    ];

    public function receipt()
    {
        return $this->belongsTo(PurchaseReceipt::class, 'purchase_receipt_id');
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
