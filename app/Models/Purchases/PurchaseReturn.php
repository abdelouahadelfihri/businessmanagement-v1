<?php

namespace App\Models\Purchases;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MasterData\Warehouse;
use App\Models\MasterData\StockMovement;

class PurchaseReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_receipt_id',
        'supplier_id',
        'return_number',
        'date',
        'warehouse_id',
        'reason',
        'subtotal',
        'tax',
        'total',
        'status',
    ];

    public function supplier()
    {
        return $this->belongsTo(\App\Models\MasterData\Supplier::class);
    }

    public function receipt()
    {
        return $this->belongsTo(\App\Models\Purchases\PurchaseReceipt::class, 'purchase_receipt_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function lines()
    {
        return $this->hasMany(PurchaseReturnLine::class, 'purchase_return_id');
    }

    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'source');
    }
}