<?php

namespace App\Models\Purchases;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MasterData\Product;

class PurchaseRequestLine extends Model
{
    use HasFactory;

    protected $table = 'purchase_request_lines';

    protected $fillable = [
        'purchase_request_id',
        'product_id',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'total_price',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($line) {
            $line->total_price = $line->quantity * $line->unit_price;
        });

        static::saved(function ($line) {
            $line->purchaseRequest->update([
                'total_amount' => $line->purchaseRequest->lines()->sum('total_price'),
            ]);
        });

        static::deleted(function ($line) {
            $line->purchaseRequest->update([
                'total_amount' => $line->purchaseRequest->lines()->sum('total_price'),
            ]);
        });
    }

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}