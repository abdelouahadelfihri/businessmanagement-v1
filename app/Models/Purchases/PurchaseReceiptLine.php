<?php

namespace App\Models\Purchases;

use Illuminate\Database\Eloquent\Model;
use App\Models\Purchases\PurchaseReceipt;
use App\Models\MasterData\Product;

class PurchaseReceiptLine extends Model
{
    protected $fillable = [
        'receipt_id',
        'product_id',
        'quantity',
    ];

    public function receipt()
    {
        return $this->belongsTo(PurchaseReceipt::class, 'receipt_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}